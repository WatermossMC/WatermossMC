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
    RequestNetworkSettings,
    ResourcePackClientResponse,
    ResourcePackStack,
    ResourcePacksInfo,
    ServerToClientHandshake,
    SetTime,
    SpawnPosition,
    StartGame
};
use WatermossMC\Network\RakNet;
use WatermossMC\Network\Session;
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

        $offset = 0;
        $count = 0;

        while ($offset < $decodedLen) {
            try {
                $len = Binary::readVarInt($data, $offset);

                if ($len === 0) {
                    continue;
                }

                if ($len < 0 || $offset + $len > $decodedLen) {
                    Logger::warning("Packet length mismatch at offset {$offset}. Len: {$len}, Remaining: " . ($decodedLen - $offset));
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
                case 0xC1: // RequestNetworkSettings (193)
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

                    $session->setMcpeState(Session::MC_NETWORK);
                    Logger::debug("[0xC1] Compression enabled. State -> MC_NETWORK");
                    return;

                case 0x01: // Login (01)
                    Logger::debug("[0x01] Login packet received.");

                    if ($session->getMcpeState() !== Session::MC_NETWORK) {
                        Logger::warning("[0x01] Ignored: Session state is not MC_NETWORK.");
                        return;
                    }
                    $loginData = Login::read($packet, $o);

                    $payload = $loginData['payload'] ?? null;
                    $clientIdentityKey = $loginData['identityPublicKey'] ?? null;

                    if (!\is_array($payload) || empty($clientIdentityKey)) {
                        Logger::error("[0x01] Login failed: Invalid payload or missing identity key.");
                        Disconnect::send($session, $socket, "Invalid login payload");
                        return;
                    }
                    $clientProtocol = $loginData['protocol'] ?? 0;
					if ($clientProtocol !== 890) {
					    Disconnect::send($session, $socket, "Outdated client");
					    return;
					}

                    $chainData = $payload['ExtraData'] ?? $payload;
                    $uuid = $chainData['identity'] ?? null;
                    $name = $chainData['displayName'] ?? 'unknown';

                    Logger::info("Login attempt: {$name} (UUID: {$uuid})");

                    Logger::debug("[0x01] Processing crypto keys...");
                    $clientPem = Crypto::bedrockIdentityKeyToPem($clientIdentityKey);
                    $session->setClientPublicKey($clientPem);
                    $session->setLoginData((string)$uuid, (string)$name, null);

                    $keys = Crypto::generateKeyPair();
                    $session->setServerKeys($keys);

                    $serverSalt = random_bytes(16);
                    $sharedSecret = Crypto::deriveSecret(
                        $keys['private'],
                        $session->getClientPublicKey()
                    );
                    Logger::debug("[0x01] Shared secret derived.");

                    $serverPublicB64 = Crypto::pemToBase64($keys['public']);
                    $jwt = self::buildServerHandshakeJwt(
                        $serverPublicB64,
                        $keys['private'],
                        $serverSalt
                    );
                    var_dump($jwt);

                    ServerToClientHandshake::send($session, $socket, $jwt);
                    Logger::debug("[0x03] ServerToClientHandshake sent.");

                    [$key, $iv] = Crypto::deriveAes($sharedSecret, $serverSalt);
                    $session->setPendingEncryption($key, $iv);
                    $session->enablePendingEncryption();
                    $session->setWaitingHandshakeAck(true);
                    RakNet::flush($session, $socket);

                    $session->setMcpeState(Session::MC_LOGIN);
                    Logger::debug("[0x03] Encryption Enabled. State -> MC_LOGIN");

                    return;

                case 0x04: // ClientToServerHandshake
                    Logger::debug("[0x04] ClientToServerHandshake received.");

                    if (!$session->isWaitingHandshakeAck()) {
                        Logger::warning("[0x04] Unexpected packet. Not waiting for ACK.");
                        return;
                    }

                    ClientToServerHandshake::read($packet, $o);

                    $session->setWaitingHandshakeAck(false);

                    Logger::info("Encryption ENABLED. Handshake connection secure.");

                    PlayStatus::sendSuccess($session, $socket);

                    ResourcePacksInfo::send($session, $socket);
                    RakNet::flush($session, $socket);
                    $session->setMcpeState(Session::MC_RESOURCE);
                    Logger::debug("[0x03] Sent PacksInfo. State -> MC_RESOURCE");

                    return;

                case 0x08: // ResourcePackClientResponse
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
                                ResourcePackStack::send($session, $socket);
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
                    Logger::debug("Unhandled Packet ID: 0x{$pidHex}");
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
            self::$world = new World('world', 12345);
            Logger::debug("World created with seed 12345.");
        }

        $s->setMcpeState(Session::MC_PLAY);

        StartGame::send($s, $sock);
        RakNet::flush($s, $sock);
        PlayStatus::sendPlayerSpawn($s, $sock);

        SetTime::send($s, $sock);
        SpawnPosition::send($s, $sock);

        Logger::debug("Sending chunks...");
        $chunkCount = 0;
        for ($x = -2; $x <= 2; $x++) {
            for ($z = -2; $z <= 2; $z++) {
                $chunk = self::$world->getChunk($x, $z);
                if ($chunk) {
                    LevelChunk::send($s, $sock, $x, $z, $chunk->encode());
                    $chunkCount++;
                }
            }
        }
        Logger::debug("Sent {$chunkCount} chunks.");

        PlayerList::send($s, $sock);

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

        $payload = json_encode([
            "salt" => base64_encode($salt),
        ], \JSON_UNESCAPED_SLASHES);

        $h = $toUrlSafe($header);
        $p = $toUrlSafe($payload);
        $dataToSign = "$h.$p";

        $sigDer = '';
        if (!openssl_sign($dataToSign, $sigDer, $priv, \OPENSSL_ALGO_SHA384)) {
            throw new \RuntimeException("OpenSSL sign failed");
        }

        $sigRaw = Crypto::derToSignature($sigDer, 48);
        var_dump(bin2hex($sigRaw));

        return "$h.$p." . $toUrlSafe($sigRaw);
    }
}
