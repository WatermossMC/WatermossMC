<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft;

use Socket;
use Throwable;
use WatermossMC\Binary\Binary;
use WatermossMC\Crypto\Crypto;
use WatermossMC\Minecraft\Packets\{
    AddPlayer,
    ClientToServerHandshake,
    Disconnect,
    LevelChunk,
    Login,
    NetworkSettings,
    PlayStatus,
    PlayerList,
    ProtocolInfo,
    RequestNetworkSettings,
    ResourcePackClientResponse,
    ResourcePackStack,
    ResourcePacksInfo,
    ServerToClientHandshake,
    SetTime,
    SpawnPosition,
    StartGame
};
use WatermossMC\Minecraft\PlayerManager;
use WatermossMC\Network\RakNet;
use WatermossMC\Network\Session;
use WatermossMC\Util\Config;
use WatermossMC\Util\Logger;

final class PacketHandler
{
    private static ?World $world = null;

    public static function handleBatch(string $data, Session $session, Socket $socket): void
    {
        $rawDataLen = \strlen($data);

        try {
            $data = $session->decodeInbound($data);
        } catch (Throwable $e) {
            Logger::error("Decode inbound failed: {$e->getMessage()}");
            return;
        }

        $decodedLen = \strlen($data);
        Logger::debug("Batch received: {$rawDataLen} bytes (Decoded: {$decodedLen} bytes)");
        $headHex = bin2hex(substr($data, 0, min(32, $decodedLen)));
        Logger::info("Batch head (hex, first 32 bytes): {$headHex}");

        $offset = 0;
        $count = 0;

        while ($offset < $decodedLen) {
            try {
                $len = Binary::readVarInt($data, $offset);

                $peekNext = bin2hex(substr($data, $offset, min(16, $decodedLen - $offset)));
                $peekPrev = bin2hex(substr($data, max(0, $offset - 8), min(8, $offset)));
                Logger::debug("Packet len read: {$len} at offset {$offset}. Next: {$peekNext}. Prev: {$peekPrev}");

                if ($len === 0) {
                    continue;
                }

                if ($len < 0 || $offset + $len > $decodedLen) {
                    Logger::warning("Packet length mismatch at offset {$offset}. Len: {$len}, Remaining: " . ($decodedLen - $offset) . ". PeekNext: {$peekNext}");
                    break;
                }

                $packet = substr($data, $offset, $len);
                $offset += $len;

                $count++;
                self::handlePacket($packet, $session, $socket);

            } catch (Throwable $e) {
                Logger::error("Batch packet processing error @ offset {$offset}: {$e->getMessage()}");
                break;
            }
        }

        Logger::debug("Batch processing complete. Handled {$count} packets.");
        RakNet::flush($session, $socket);
    }

    private static function handlePacket(string $packet, Session $session, Socket $socket): void
    {
        try {
            $o = 0;
            $pid = Binary::readVarInt($packet, $o);
            $pidHex = strtoupper(dechex($pid));

            Logger::debug("RX Packet: 0x{$pidHex} (State: " . $session->getMcpeState() . ")");

            switch ($pid) {
                case ProtocolInfo::REQUEST_NETWORK_SETTINGS_PACKET: // RequestNetworkSettings (193)
                    Logger::debug("[0xC1] RequestNetworkSettings received.");

                    if ($session->getMcpeState() !== Session::MC_NONE) {
                        Logger::warning("[0xC1] Ignored: Session state is not NONE.");
                        return;
                    }

                    RequestNetworkSettings::read($packet, $o, $session, $socket);
                    NetworkSettings::send($session, $socket);

                    RakNet::flush($session, $socket);

                    $session->markNetworkSettingsSent();

                    $session->enableOutboundCompression(NetworkSettings::COMPRESS_EVERYTHING);
                    // Allow client to send compressed inbound data after NetworkSettings
                    $session->enableInboundCompression();

                    $session->setMcpeState(Session::MC_NETWORK);
                    Logger::debug("[0xC1] Compression enabled. State -> MC_NETWORK");
                    return;

                case ProtocolInfo::LOGIN_PACKET: // Login (01)
                    Logger::debug("[0x01] Login packet received.");

                    if ($session->getMcpeState() !== Session::MC_NETWORK) {
                        Logger::warning("[0x01] Ignored: Session state is not MC_NETWORK.");
                        return;
                    }
                    
                    try {
                        $loginData = Login::read($packet, $o);
                    } catch (Throwable $e) {
                        Logger::error("[0x01] Login::read() failed: {$e->getMessage()}");
                        Disconnect::send($session, $socket, "Login parsing failed");
                        return;
                    }

                    $payload = $loginData['payload'];
                    $clientIdentityKey = $loginData['identityPublicKey'];

                    Logger::debug("[0x01] Payload type: " . gettype($payload) . ", payload empty: " . (empty($payload) ? 'YES' : 'NO'));
                    Logger::debug("[0x01] IdentityKey present: " . ($clientIdentityKey ? 'YES' : 'NO'));

                    if (empty($clientIdentityKey)) {
                        Logger::error("[0x01] Login failed: Missing identity key.");
                        Disconnect::send($session, $socket, "Invalid login payload");
                        return;
                    }
                    $clientProtocol = $loginData['protocol'];
                    Logger::debug("[0x01] Protocol version: {$clientProtocol}");
					if ($clientProtocol !== ProtocolInfo::CURRENT_PROTOCOL) {
					    Logger::error("[0x01] Protocol mismatch. Client: {$clientProtocol}, Expected: " . ProtocolInfo::CURRENT_PROTOCOL);
					    PlayStatus::sendFailedClient($session, $socket);
					    Disconnect::send($session, $socket, "Outdated client");
					    return;
					}

                    $chainData = $payload['ExtraData'] ?? $payload;
                    $uuid = null;
                    $name = 'unknown';

                    if (is_array($chainData)) {
                        if (isset($chainData['identity']) && is_string($chainData['identity'])) {
                            $uuid = $chainData['identity'];
                        }
                        if (isset($chainData['displayName']) && is_string($chainData['displayName'])) {
                            $name = $chainData['displayName'];
                        }
                    }

                    Logger::info("Login attempt: {$name} (UUID: " . ($uuid ?? 'unknown') . ")");

                    try {
                        Logger::debug("[0x01] Processing crypto keys...");
                        $clientPem = Crypto::bedrockIdentityKeyToPem($clientIdentityKey);
                        Logger::debug("[0x01] Client PEM generated");
                        
                        $session->setClientPublicKey($clientPem);
                        $session->setLoginData((string)$uuid, (string)$name, null);

                        $keys = Crypto::generateKeyPair();
                        Logger::debug("[0x01] Server keypair generated");
                        
                        $session->setServerKeys($keys);

                        $serverSalt = random_bytes(16);
                        Logger::debug("[0x01] Server salt generated");
                        
                        $sharedSecret = Crypto::deriveSecret(
                            $keys['private'],
                            $session->getClientPublicKey()
                        );
                        Logger::debug("[0x01] Shared secret derived.");

                        $serverPublicB64 = Crypto::pemToBase64($keys['public']);
                        Logger::debug("[0x01] Server public key converted to base64");
                        Logger::debug("[0x01] Base64" . $serverPublicB64);
                        
                        $jwt = self::buildServerHandshakeJwt(
                            $serverPublicB64,
                            $keys['private'],
                            $serverSalt
                        );
                        Logger::debug("[0x01] Server handshake JWT built, length: " . strlen($jwt));
                        Logger::debug($jwt);

                        try {
                            $parts = explode('.', $jwt);
                            if (count($parts) !== 3) {
                                throw new \RuntimeException('JWT must have 3 parts');
                            }

                            [$hdr, $pld, $sig] = $parts;

                            $pad = fn(string $s): string => $s . str_repeat('=', (4 - (strlen($s) % 4)) % 4);
                            $decodedHdr = json_decode(base64_decode(strtr($pad($hdr), '-_', '+/')), true);
                            $decodedPld = json_decode(base64_decode(strtr($pad($pld), '-_', '+/')), true);
                            $sigRaw = base64_decode(strtr($pad($sig), '-_', '+/'), true);
                            if (!is_string($sigRaw)) {
                                throw new \RuntimeException('Invalid signature payload');
                            }

                            Logger::debug('[0x01] Handshake JWT header: ' . ($decodedHdr === null ? 'INVALID' : json_encode($decodedHdr)));
                            Logger::debug('[0x01] Handshake JWT payload: ' . ($decodedPld === null ? 'INVALID' : json_encode($decodedPld)));
                            Logger::debug('[0x01] Handshake JWT sig length: ' . strlen($sigRaw) . " bytes");

                            if (strlen($sigRaw) !== 96) {
                                Logger::error('[0x01] Unexpected handshake signature length: ' . strlen($sigRaw) . ' (expected 96)');
                                Disconnect::send($session, $socket, 'Handshake signature invalid');
                                RakNet::flush($session, $socket);
                                return;
                            }

                            // Verify signature locally: convert raw r||s to DER and verify using server public key
                            try {
                                $dataToSign = $hdr . '.' . $pld;
                                $sigDer = \WatermossMC\Crypto\Crypto::signatureToDer($sigRaw);
                                $pubPem = $keys['public'];
                                $verify = openssl_verify($dataToSign, $sigDer, $pubPem, \OPENSSL_ALGO_SHA384);
                                if ($verify !== 1) {
                                    Logger::error('[0x01] Local signature verification failed (openssl_verify != 1).');
                                    Disconnect::send($session, $socket, 'Handshake signature verification failed');
                                    RakNet::flush($session, $socket);
                                    return;
                                }
                                Logger::debug('[0x01] Local signature verification succeeded');
                            } catch (\Throwable $e) {
                                Logger::error('[0x01] Signature verification error: ' . $e->getMessage());
                                Disconnect::send($session, $socket, 'Handshake verification error');
                                RakNet::flush($session, $socket);
                                return;
                            }
                        } catch (\Throwable $e) {
                            Logger::error('[0x01] JWT diagnostics failed: ' . $e->getMessage());
                            Disconnect::send($session, $socket, 'Handshake construction failed');
                            RakNet::flush($session, $socket);
                            return;
                        }

                        ServerToClientHandshake::send($session, $socket, $jwt);
                        Logger::debug("[0x03] ServerToClientHandshake sent, attempting to flush...");
                        Logger::debug("[0x01] RakNet flushed after ServerToClientHandshake");

                        RakNet::flush($session, $socket);
                        usleep(50000);

                        [$key, $iv] = Crypto::deriveAes($sharedSecret, $serverSalt);
                        Logger::debug("[0x01] AES key and IV derived");
                        
                        $session->setPendingEncryption($key, $iv);
                        Logger::debug("[0x01] Pending encryption set");
                          
						$session->enablePendingEncryption();
						Logger::debug("[0x01] Pending encryption enabled");

                        $session->setWaitingHandshakeAck(true);
                        $session->setMcpeState(Session::MC_LOGIN);
                        Logger::debug("[0x03] State -> MC_LOGIN");

                    } catch (Throwable $e) {
                        Logger::error("[0x01] Crypto/handshake processing failed: {$e->getMessage()}");
                        Logger::debug($e->getTraceAsString());
                        Disconnect::send($session, $socket, "Server handshake failed");
                        RakNet::flush($session, $socket);
                        return;
                    }

                    return;

                case ProtocolInfo::CLIENT_TO_SERVER_HANDSHAKE_PACKET: // ClientToServerHandshake
                    Logger::debug("[0x04] ClientToServerHandshake received.");

                    if (!$session->hasWaitingHandshakeAck()) {
                        Logger::warning("[0x04] Unexpected packet. Not waiting for ACK. Current state: " . $session->getMcpeState());
                        return;
                    }

                    try {
                        ClientToServerHandshake::read($packet, $o);
                        Logger::debug("[0x04] ClientToServerHandshake parsed successfully");
                    } catch (Throwable $e) {
                        Logger::error("[0x04] Failed to parse ClientToServerHandshake: {$e->getMessage()}");
                        return;
                    }

                    $session->setWaitingHandshakeAck(false);
					$session->enableInboundCompression();
					Logger::debug("[0x04] Enable inbound compression");


                    Logger::info("Encryption ENABLED. Handshake connection secure.");

                    PlayStatus::sendSuccess($session, $socket);
                    RakNet::flush($session, $socket);
                    ResourcePacksInfo::send($session, $socket, [], false);
                    RakNet::flush($session, $socket);
                    $session->setMcpeState(Session::MC_RESOURCE);
                    Logger::debug("[0x03] Sent PacksInfo. State -> MC_RESOURCE");

                    return;

                case ProtocolInfo::RESOURCE_PACK_CLIENT_RESPONSE_PACKET: // ResourcePackClientResponse
                    if ($session->getMcpeState() !== Session::MC_RESOURCE) {
                        return;
                    }

                    $rp = ResourcePackClientResponse::read($packet, $o);
                    $status = $rp['status'];

                    Logger::debug("[0x08] ResourcePack Response Status: {$status}");

                    match ($status) {
                        ResourcePackClientResponse::STATUS_HAVE_ALL_PACKS =>
                            (function () use ($session, $socket): void {
                                Logger::debug("Client has all packs. Sending stack.");
                                ResourcePackStack::send($session, $socket, [], []);
                                RakNet::flush($session, $socket);
                            })(),

                        ResourcePackClientResponse::STATUS_COMPLETED =>
                            (function () use ($session, $socket): void {
                                Logger::debug("Resource packs completed. Starting game sequence...");
                                self::startPlay($session, $socket);
                            })(),

                        default => Logger::debug("Unhandled ResourcePack status: {$status}")
                    };

                    return;

                default:
                    Logger::debug("Unhandled Packet ID: 0x{$pidHex} in state " . $session->getMcpeState());
                    break;
            }

        } catch (Throwable $e) {
            Logger::error("Packet handling error [PID: 0x" . ($pidHex ?? 'UNKNOWN') . "]: {$e->getMessage()}");
            Logger::debug($e->getTraceAsString());
            Disconnect::send($session, $socket, "Internal Server Error");
            RakNet::flush($session, $socket);
        }
    }

    private static function startPlay(Session $s, Socket $sock): void
    {
        Logger::debug("Initializing world sequence...");

        if (self::$world === null) {
            self::$world = new World(
                Config::getString('level_name', 'world'),
                Config::getInt('level_seed', 12345)
            );
            Logger::debug("World created with seed " . self::$world->seed . ".");
        }

        if (PlayerManager::get($s) === null) {
            PlayerManager::add($s, $s->getPlayerName());
        }

        $s->setMcpeState(Session::MC_PLAY);
        $s->setPosition(0.0, 64.0, 0.0);

        StartGame::send($s, $sock);
        PlayStatus::sendPlayerSpawn($s, $sock);
        SetTime::send($s, $sock);
        SpawnPosition::send($s, $sock);

        Logger::debug("Sending chunks...");
        $chunkCount = 0;
        for ($x = -2; $x <= 2; $x++) {
            for ($z = -2; $z <= 2; $z++) {
                $chunk = self::$world->getChunk($x, $z);
                LevelChunk::send($s, $sock, $x, $z, $chunk->encode(), $chunk->getSubChunkCount());
                $chunkCount++;
            }
        }
        Logger::debug("Sent {$chunkCount} chunks.");

        Logger::info("Player " . $s->getPlayerName() . " joined the game successfully!");
        RakNet::flush($s, $sock);
    }

    private static function buildServerHandshakeJwt(string $pubBase64, string $priv, string $salt): string
    {
        $toUrlSafe = fn (string $data): string => rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

        $header = json_encode([
            "alg" => "ES384",
            "x5u" => $pubBase64,
        ], \JSON_UNESCAPED_SLASHES);
        if ($header === false) {
            throw new \RuntimeException("Failed to encode header");
        }

        $payload = json_encode([
            "salt" => base64_encode($salt),
        ], \JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new \RuntimeException("Failed to encode payload");
        }

        $h = $toUrlSafe($header);
        $p = $toUrlSafe($payload);
        $dataToSign = "$h.$p";

        $sigDer = '';
        if (!openssl_sign($dataToSign, $sigDer, $priv, \OPENSSL_ALGO_SHA384)) {
            throw new \RuntimeException("OpenSSL sign failed");
        }

        /** @var string $sigDer */
        $sigRaw = Crypto::derToSignature($sigDer, 48);

        return "$h.$p." . $toUrlSafe($sigRaw);
    }
}