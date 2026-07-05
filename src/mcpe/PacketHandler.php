<?php

declare(strict_types=1);

namespace watermossmc\mcpe;

use Socket;
use Throwable;
use watermossmc\binary\Binary;
use watermossmc\crypto\Crypto;
use watermossmc\event\PlayerJoinEvent;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\{
    AvailableActorIdentifiers,
    AvailableCommands,
    BiomeDefinitionList,
    ClientToServerHandshake,
    CraftingData,
    CreativeContent,
    Disconnect,
    InventoryContent,
    InventorySlot,
    ItemRegistry,
    LevelChunk,
    Login,
    NetworkSettings,
    PlayStatus,
    PlayerHotbar,
    PlayerList,
    ProtocolInfo,
    RequestNetworkSettings,
    ResourcePackClientResponse,
    ResourcePackStack,
    ResourcePacksInfo,
    ServerToClientHandshake,
    SetActorData,
    SpawnPosition,
    StartGame,
    UpdateAbilities,
    UpdateAdventureSettings,
    UpdateAttributes,
};
use watermossmc\player\PlayerManager;
use watermossmc\Server;
use watermossmc\util\Config;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class PacketHandler
{
    private static ?World $world = null;

    private static ?Server $server = null;

    public static function setServer(Server $server): void
    {
        self::$server = $server;
    }

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
                    $clientIdentityKey = $loginData['ecdhPublicKey'] ?? $loginData['identityPublicKey'];

                    Logger::debug("[0x01] Payload type: " . \gettype($payload) . ", payload empty: " . (empty($payload) ? 'YES' : 'NO'));
                    Logger::debug("[0x01] IdentityKey present: " . ($clientIdentityKey ? 'YES' : 'NO'));

                    if (empty($clientIdentityKey)) {
                        Logger::error("[0x01] Login failed: Missing identity key.");
                        Disconnect::send($session, $socket, "Invalid login payload");
                        return;
                    }
                    $clientProtocol = $loginData['protocol'];
                    Logger::debug("[0x01] Protocol version: {$clientProtocol}");
                    if ($clientProtocol < ProtocolInfo::CURRENT_PROTOCOL) {
                        Logger::error("[0x01] Protocol mismatch. Client: {$clientProtocol}, Expected: " . ProtocolInfo::CURRENT_PROTOCOL);
                        PlayStatus::sendFailedClient($session, $socket);
                        Disconnect::send($session, $socket, "Outdated client");
                        return;
                    } elseif ($clientProtocol > ProtocolInfo::CURRENT_PROTOCOL) {
                        Logger::error("[0x01] Protocol mismatch. Client: {$clientProtocol}, Expected: " . ProtocolInfo::CURRENT_PROTOCOL);
                        PlayStatus::sendFailedClient($session, $socket);
                        Disconnect::send($session, $socket, "Outdated server");
                        return;
                    }

                    $chainData = $payload['ExtraData'] ?? $payload;
                    $uuid = null;
                    $name = 'unknown';

                    if (\is_array($chainData)) {
                        if (isset($chainData['identity']) && \is_string($chainData['identity'])) {
                            $uuid = $chainData['identity'];
                        }
                        if (isset($chainData['displayName']) && \is_string($chainData['displayName'])) {
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
                        Logger::debug("[0x01] Server handshake JWT built, length: " . \strlen($jwt));
                        Logger::debug($jwt);

                        try {
                            $parts = explode('.', $jwt);
                            if (\count($parts) !== 3) {
                                throw new \RuntimeException('JWT must have 3 parts');
                            }

                            [$hdr, $pld, $sig] = $parts;

                            $pad = fn (string $s): string => $s . str_repeat('=', (4 - (\strlen($s) % 4)) % 4);
                            $decodedHdr = json_decode(base64_decode(strtr($pad($hdr), '-_', '+/'), true), true);
                            $decodedPld = json_decode(base64_decode(strtr($pad($pld), '-_', '+/'), true), true);
                            $sigRaw = base64_decode(strtr($pad($sig), '-_', '+/'), true);
                            if (!\is_string($sigRaw)) {
                                throw new \RuntimeException('Invalid signature payload');
                            }

                            Logger::debug('[0x01] Handshake JWT header: ' . ($decodedHdr === null ? 'INVALID' : json_encode($decodedHdr)));
                            Logger::debug('[0x01] Handshake JWT payload: ' . ($decodedPld === null ? 'INVALID' : json_encode($decodedPld)));
                            Logger::debug('[0x01] Handshake JWT sig length: ' . \strlen($sigRaw) . " bytes");

                            if (\strlen($sigRaw) !== 96) {
                                Logger::error('[0x01] Unexpected handshake signature length: ' . \strlen($sigRaw) . ' (expected 96)');
                                Disconnect::send($session, $socket, 'Handshake signature invalid');
                                RakNet::flush($session, $socket);
                                return;
                            }

                            // Verify signature locally: convert raw r||s to DER and verify using server public key
                            try {
                                $dataToSign = $hdr . '.' . $pld;
                                $sigDer = \watermossmc\crypto\Crypto::signatureToDer($sigRaw);
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

                        $key = Crypto::deriveAes($sharedSecret, $serverSalt);
                        Logger::debug("[0x01] AES key and IV derived");
                        Logger::debug("[0x01] AES key=" . bin2hex($key));

                        $session->setPendingEncryption($key);
                        $session->enablePendingEncryption();
                        Logger::debug("[0x01] Pending decryption enabled (inbound)");
                        Logger::debug("[0x01] Pending encryption set");

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
                    $session->finalizeEncryption();
                    Logger::debug("[0x04] Encryption finalized");
                    Logger::debug("[0x04] outEncryption active: " . ($session->isEncryptionEnabled() ? 'YES' : 'NO'));
                    Logger::info("Encryption ENABLED. Handshake connection secure.");

                    PlayStatus::sendSuccess($session, $socket);
                    RakNet::flush($session, $socket);
                    Logger::debug("[ResourcePacks] Sending ResourcePacksInfo...");
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
                                ResourcePackStack::send($session, $socket, []);
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

                case ProtocolInfo::CLIENT_CACHE_STATUS_PACKET: // ClientCacheStatus
                    Logger::debug("[0x81] ClientCacheStatus received (ignored)");
                    return;

                case ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET: // RequestChunkRadius
                    Logger::debug("[0x45] RequestChunkRadius received");
                    self::sendSpawnChunks($session, $socket);

                    PlayStatus::sendPlayerSpawn($session, $socket);
                    $session->enterPlay();

                    RakNet::flush($session, $socket);
                    return;

                case ProtocolInfo::MOVE_PLAYER_PACKET:
                    $moveData = \watermossmc\mcpe\protocol\MovePlayer::read($packet, $o);
                    $player = \watermossmc\player\PlayerManager::get($session);
                    if ($player === null) {
                        return;
                    }

                    $from = $player->getLocation();

                    // Update player coordinates based on packet data
                    $player->x = $moveData['x'];
                    $player->y = $moveData['y'];
                    $player->z = $moveData['z'];
                    $player->yaw = $moveData['yaw'];
                    $player->pitch = $moveData['pitch'];
                    $player->headYaw = $moveData['headYaw'];

                    $to = $player->getLocation();

                    if (self::$server !== null) {
                        self::$server->dispatch(new \watermossmc\event\PlayerMoveEvent($player, $from, $to));
                    }
                    return;
            }

        } catch (Throwable $e) {
            Logger::error("Packet handling error [PID: 0x" . ($pidHex ?? '??') . "]: {$e->getMessage()}");
            Logger::debug($e->getTraceAsString());
            Disconnect::send($session, $socket, "Internal Server Error");
            RakNet::flush($session, $socket);
        }
    }

    private static function startPlay(Session $s, Socket $sock): void
    {
        Logger::debug("Initializing world sequence...");

        $world = self::getWorld();

        if (self::$server === null) {
            throw new \RuntimeException("Server not initialized");
        }
        $player = PlayerManager::add($s, $s->getPlayerName(), self::$server);
        self::$server->dispatch(new PlayerJoinEvent(self::$server, $player));

        $spawn = $world->getSpawnPosition();
        $playerSpawn = $world->getPlayerSpawnPosition();

        $s->setMcpeState(Session::MC_PRESPAWN);
        $s->setPosition($playerSpawn['x'], $playerSpawn['y'], $playerSpawn['z']);

        StartGame::send($s, $sock, $world);
        SpawnPosition::send($s, $sock, $spawn['x'], $spawn['y'], $spawn['z']);
        ItemRegistry::send($s, $sock);
        AvailableActorIdentifiers::send($s, $sock);
        BiomeDefinitionList::send($s, $sock);
        UpdateAttributes::send($s, $sock);
        AvailableCommands::send($s, $sock);
        UpdateAbilities::send($s, $sock);
        UpdateAdventureSettings::send($s, $sock);
        // MobEffect
        SetActorData::sendPlayer($s, $sock);
        InventoryContent::sendEmpty($s, $sock, InventoryContent::WINDOW_INVENTORY);
        InventoryContent::sendEmpty($s, $sock, InventoryContent::WINDOW_ARMOR);
        InventorySlot::sendEmpty($s, $sock, InventoryContent::WINDOW_INVENTORY, 0);
        PlayerHotbar::send($s, $sock);
        CreativeContent::sendEmpty($s, $sock);
        CraftingData::sendEmpty($s, $sock);
        PlayerList::sendAdd($s, $sock);
        RakNet::flush($s, $sock);

        $s->setWaitingRequestChunkRadiusAck(true);

        Logger::debug("Waiting for chunk radius request");
    }

    public static function saveWorld(): void
    {
        if (self::$server !== null) {
            try {
                self::$server->saveWorld();
                Logger::info('World saved successfully.');
            } catch (Throwable $e) {
                Logger::error('World save failed: ' . $e->getMessage());
            }

            return;
        }

        if (self::$world === null) {
            Logger::debug('No loaded world present to save.');
            return;
        }

        try {
            self::$world->save();
            Logger::info('World saved successfully.');
        } catch (Throwable $e) {
            Logger::error('World save failed: ' . $e->getMessage());
        }
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

    private static function getWorld(): World
    {
        if (self::$server !== null) {
            return self::$server->getWorld();
        }

        if (self::$world === null) {
            $worldName = Config::getString('level_name', 'world');
            $worldFolder = Config::getString('world_folder', $worldName);
            $worldPath = \dirname(__DIR__, 2) . '/' . $worldFolder;

            self::$world = World::load($worldPath, $worldName, Config::getInt('level_seed', 12345));
            Logger::debug("World loaded from {$worldPath} with seed " . self::$world->seed . ".");
        }

        return self::$world;
    }

    private static function sendSpawnChunks(Session $s, Socket $sock): void
    {
        try {
            $world = self::getWorld();
        } catch (Throwable $e) {
            Logger::warning('Cannot send spawn chunks before world is loaded: ' . $e->getMessage());
            return;
        }

        $spawn = $world->getSpawnPosition();
        $centerChunkX = self::blockToChunk($spawn['x']);
        $centerChunkZ = self::blockToChunk($spawn['z']);

        for ($x = $centerChunkX - 2; $x <= $centerChunkX + 2; $x++) {
            for ($z = $centerChunkZ - 2; $z <= $centerChunkZ + 2; $z++) {
                $chunk = $world->getChunk($x, $z);

                LevelChunk::send(
                    $s,
                    $sock,
                    $x,
                    $z,
                    $chunk->encode(),
                    $chunk->getSubChunkCount()
                );
            }
        }
    }

    private static function blockToChunk(int $coordinate): int
    {
        $result = intdiv($coordinate, 16);

        if ($coordinate < 0 && $coordinate % 16 !== 0) {
            $result--;
        }

        return $result;
    }
}
