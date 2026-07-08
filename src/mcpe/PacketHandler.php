<?php

<<<<<<< HEAD
=======
/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\mcpe;

<<<<<<< HEAD
=======
use RuntimeException;
>>>>>>> 866a1c0 (...)
use Socket;
use Throwable;
use watermossmc\binary\Binary;
use watermossmc\crypto\Crypto;
use watermossmc\event\PlayerJoinEvent;
<<<<<<< HEAD
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
=======
use watermossmc\event\PlayerMoveEvent;
use watermossmc\event\PlayerQuitEvent;
use watermossmc\mcpe\network\RakNet;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\AddPlayer;
use watermossmc\mcpe\protocol\AvailableActorIdentifiers;
use watermossmc\mcpe\protocol\AvailableCommands;
use watermossmc\mcpe\protocol\BiomeDefinitionList;
use watermossmc\mcpe\protocol\ClientCacheStatus;
use watermossmc\mcpe\protocol\ClientToServerHandshake;
use watermossmc\mcpe\protocol\CraftingData;
use watermossmc\mcpe\protocol\CreativeContent;
use watermossmc\mcpe\protocol\Disconnect;
use watermossmc\mcpe\protocol\InventoryContent;
use watermossmc\mcpe\protocol\ItemRegistry;
use watermossmc\mcpe\protocol\LevelChunk;
use watermossmc\mcpe\protocol\Login;
use watermossmc\mcpe\protocol\MobEffect;
use watermossmc\mcpe\protocol\NetworkSettings;
use watermossmc\mcpe\protocol\PlayerHotbar;
use watermossmc\mcpe\protocol\PlayerList;
use watermossmc\mcpe\protocol\PlayStatus;
use watermossmc\mcpe\protocol\ProtocolInfo;
use watermossmc\mcpe\protocol\RequestNetworkSettings;
use watermossmc\mcpe\protocol\ResourcePackClientResponse;
use watermossmc\mcpe\protocol\ResourcePacksInfo;
use watermossmc\mcpe\protocol\ResourcePackStack;
use watermossmc\mcpe\protocol\ServerToClientHandshake;
use watermossmc\mcpe\protocol\SetActorData;
use watermossmc\mcpe\protocol\SetSpawnPosition;
use watermossmc\mcpe\protocol\SetTime;
use watermossmc\mcpe\protocol\StartGame;
use watermossmc\mcpe\protocol\UpdateAbilities;
use watermossmc\mcpe\protocol\UpdateAdventureSettings;
use watermossmc\mcpe\protocol\UpdateAttributes;
use watermossmc\mcpe\protocol\VoxelShapes;
>>>>>>> 866a1c0 (...)
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
<<<<<<< HEAD
        Logger::debug("Batch received: {$rawDataLen} bytes (Decoded: {$decodedLen} bytes)");
        $headHex = bin2hex(substr($data, 0, min(32, $decodedLen)));
        Logger::info("Batch head (hex, first 32 bytes): {$headHex}");
=======
        Logger::debug("[Network] Batch RX: {$rawDataLen} raw -> {$decodedLen} decoded | Session: {$session->getRuntimeId()} | State: {$session->getMcpeState()}");
>>>>>>> 866a1c0 (...)

        $offset = 0;
        $count = 0;

        while ($offset < $decodedLen) {
            try {
                $len = Binary::readVarInt($data, $offset);

<<<<<<< HEAD
                $peekNext = bin2hex(substr($data, $offset, min(16, $decodedLen - $offset)));
                $peekPrev = bin2hex(substr($data, max(0, $offset - 8), min(8, $offset)));
                Logger::debug("Packet len read: {$len} at offset {$offset}. Next: {$peekNext}. Prev: {$peekPrev}");

=======
>>>>>>> 866a1c0 (...)
                if ($len === 0) {
                    continue;
                }

                if ($len < 0 || $offset + $len > $decodedLen) {
<<<<<<< HEAD
                    Logger::warning("Packet length mismatch at offset {$offset}. Len: {$len}, Remaining: " . ($decodedLen - $offset) . ". PeekNext: {$peekNext}");
=======
                    Logger::warning("[Network] Packet length mismatch @ offset {$offset}. Len: {$len}, Remaining: " . ($decodedLen - $offset));
>>>>>>> 866a1c0 (...)
                    break;
                }

                $packet = substr($data, $offset, $len);
                $offset += $len;

                $count++;
                self::handlePacket($packet, $session, $socket);

            } catch (Throwable $e) {
<<<<<<< HEAD
                Logger::error("Batch packet processing error @ offset {$offset}: {$e->getMessage()}");
=======
                Logger::error("[Network] Batch packet processing error @ offset {$offset}: {$e->getMessage()}");
>>>>>>> 866a1c0 (...)
                break;
            }
        }

<<<<<<< HEAD
        Logger::debug("Batch processing complete. Handled {$count} packets.");
=======
        Logger::debug("[Network] Batch processed: {$count} packets.");
>>>>>>> 866a1c0 (...)
        RakNet::flush($session, $socket);
    }

    private static function handlePacket(string $packet, Session $session, Socket $socket): void
    {
        try {
            $o = 0;
            $pid = Binary::readVarInt($packet, $o);
            $pidHex = strtoupper(dechex($pid));

<<<<<<< HEAD
            Logger::debug("RX Packet: 0x{$pidHex} (State: " . $session->getMcpeState() . ")");

            switch ($pid) {
=======
            Logger::debug("[RX] Packet 0x{$pidHex} | State: {$session->getMcpeState()} | Size: " . \strlen($packet) . " bytes");

            switch ($pid) {
                case ProtocolInfo::DISCONNECT_PACKET: // Disconnect (0x05)
                    Logger::debug("[0x05] Disconnect packet received.");
                    $reason = Binary::readString($packet, $o);
                    Logger::info("Player disconnected: {$reason}");

                    $player = PlayerManager::get($session);
                    if ($player !== null) {
                        Server::getInstance()?->dispatch(new PlayerQuitEvent($player));
                        PlayerManager::remove($session);
                    }

                    $session->close();
                    return;

>>>>>>> 866a1c0 (...)
                case ProtocolInfo::REQUEST_NETWORK_SETTINGS_PACKET: // RequestNetworkSettings (193)
                    Logger::debug("[0xC1] RequestNetworkSettings received.");

                    if ($session->getMcpeState() !== Session::MC_NONE) {
                        Logger::warning("[0xC1] Ignored: Session state is not NONE.");
                        return;
                    }

<<<<<<< HEAD
                    RequestNetworkSettings::read($packet, $o, $session, $socket);
=======
                    if (!RequestNetworkSettings::read($packet, $o, $session, $socket)) {
                        Logger::debug("[0xC1] Protocol mismatch detected. Disconnect sent. Aborting.");
                        return;
                    }

>>>>>>> 866a1c0 (...)
                    NetworkSettings::send($session, $socket);

                    RakNet::flush($session, $socket);

                    $session->markNetworkSettingsSent();

                    $session->enableOutboundCompression(NetworkSettings::COMPRESS_EVERYTHING);
                    // Allow client to send compressed inbound data after NetworkSettings
                    $session->enableInboundCompression();

                    $session->setMcpeState(Session::MC_NETWORK);
<<<<<<< HEAD
                    Logger::debug("[0xC1] Compression enabled. State -> MC_NETWORK");
=======
                    Logger::debug("[0xC1] Protocol verified. Compression enabled. State -> MC_NETWORK");
>>>>>>> 866a1c0 (...)
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
<<<<<<< HEAD
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
=======
                    Logger::debug("[0x01] Login packet protocol: {$clientProtocol}");

                    $name = $loginData['displayName'];
                    $uuid = $loginData['payload']['identity'] ?? '0';
                    $xuid = $loginData['payload']['XUID'] ?? '0';

                    Logger::info("Login attempt: {$name} (UUID: {$uuid}, XUID: {$xuid})");
>>>>>>> 866a1c0 (...)

                    try {
                        Logger::debug("[0x01] Processing crypto keys...");
                        $clientPem = Crypto::bedrockIdentityKeyToPem($clientIdentityKey);
                        Logger::debug("[0x01] Client PEM generated");

                        $session->setClientPublicKey($clientPem);
<<<<<<< HEAD
                        $session->setLoginData((string)$uuid, (string)$name, null);
=======
                        $session->setLoginData((string)$uuid, (string)$name, (string)$xuid);
>>>>>>> 866a1c0 (...)

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
<<<<<<< HEAD
                                throw new \RuntimeException('JWT must have 3 parts');
=======
                                throw new RuntimeException('JWT must have 3 parts');
>>>>>>> 866a1c0 (...)
                            }

                            [$hdr, $pld, $sig] = $parts;

                            $pad = fn (string $s): string => $s . str_repeat('=', (4 - (\strlen($s) % 4)) % 4);
<<<<<<< HEAD
                            $decodedHdr = json_decode(base64_decode(strtr($pad($hdr), '-_', '+/'), true), true);
                            $decodedPld = json_decode(base64_decode(strtr($pad($pld), '-_', '+/'), true), true);
                            $sigRaw = base64_decode(strtr($pad($sig), '-_', '+/'), true);
                            if (!\is_string($sigRaw)) {
                                throw new \RuntimeException('Invalid signature payload');
=======
                            $decodedHdr = json_decode(base64_decode(strtr($pad($hdr), '-_', '+/'), true) ?: '', true);
                            $decodedPld = json_decode(base64_decode(strtr($pad($pld), '-_', '+/'), true) ?: '', true);
                            $sigRaw = base64_decode(strtr($pad($sig), '-_', '+/'), true);
                            if (!\is_string($sigRaw)) {
                                throw new RuntimeException('Invalid signature payload');
>>>>>>> 866a1c0 (...)
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

<<<<<<< HEAD
                            // Verify signature locally: convert raw r||s to DER and verify using server public key
                            try {
                                $dataToSign = $hdr . '.' . $pld;
                                $sigDer = \watermossmc\crypto\Crypto::signatureToDer($sigRaw);
=======
                            try {
                                $dataToSign = $hdr . '.' . $pld;
                                $sigDer = Crypto::signatureToDer($sigRaw);
>>>>>>> 866a1c0 (...)
                                $pubPem = $keys['public'];
                                $verify = openssl_verify($dataToSign, $sigDer, $pubPem, \OPENSSL_ALGO_SHA384);
                                if ($verify !== 1) {
                                    Logger::error('[0x01] Local signature verification failed (openssl_verify != 1).');
                                    Disconnect::send($session, $socket, 'Handshake signature verification failed');
                                    RakNet::flush($session, $socket);
                                    return;
                                }
                                Logger::debug('[0x01] Local signature verification succeeded');
<<<<<<< HEAD
                            } catch (\Throwable $e) {
=======
                            } catch (Throwable $e) {
>>>>>>> 866a1c0 (...)
                                Logger::error('[0x01] Signature verification error: ' . $e->getMessage());
                                Disconnect::send($session, $socket, 'Handshake verification error');
                                RakNet::flush($session, $socket);
                                return;
                            }
<<<<<<< HEAD
                        } catch (\Throwable $e) {
=======
                        } catch (Throwable $e) {
>>>>>>> 866a1c0 (...)
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
<<<<<<< HEAD
                    RakNet::flush($session, $socket);
=======
>>>>>>> 866a1c0 (...)
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
<<<<<<< HEAD
                    Logger::debug("[0x81] ClientCacheStatus received (ignored)");
=======
                    try {
                        $data = ClientCacheStatus::read($packet, $o);
                        $session->setCacheEnabled($data['enabled']);
                        Logger::debug("[0x81] ClientCacheStatus received: " . ($data['enabled'] ? 'Enabled' : 'Disabled'));
                    } catch (Throwable $e) {
                        Logger::error("[0x81] Failed to read ClientCacheStatus: " . $e->getMessage());
                    }
>>>>>>> 866a1c0 (...)
                    return;

                case ProtocolInfo::REQUEST_CHUNK_RADIUS_PACKET: // RequestChunkRadius
                    Logger::debug("[0x45] RequestChunkRadius received");
<<<<<<< HEAD
=======

                    $world = self::getWorld();
                    $spawn = $world->getSpawnPosition();
                    SetSpawnPosition::send($session, $socket, $spawn['x'], $spawn['y'], $spawn['z']);

>>>>>>> 866a1c0 (...)
                    self::sendSpawnChunks($session, $socket);

                    PlayStatus::sendPlayerSpawn($session, $socket);
                    $session->enterPlay();

                    RakNet::flush($session, $socket);
                    return;

                case ProtocolInfo::MOVE_PLAYER_PACKET:
<<<<<<< HEAD
                    $moveData = \watermossmc\mcpe\protocol\MovePlayer::read($packet, $o);
                    $player = \watermossmc\player\PlayerManager::get($session);
=======
                    $moveData = protocol\MovePlayer::read($packet, $o);
                    $player = PlayerManager::get($session);
>>>>>>> 866a1c0 (...)
                    if ($player === null) {
                        return;
                    }

                    $from = $player->getLocation();

<<<<<<< HEAD
                    // Update player coordinates based on packet data
                    $player->x = $moveData['x'];
                    $player->y = $moveData['y'];
                    $player->z = $moveData['z'];
                    $player->yaw = $moveData['yaw'];
=======
                    $player->x = $moveData['x'];
                    $player->y = $moveData['y'];
                    $player->z = $moveData['z'];
                    $player->yaw = $moveData['headYaw'];
>>>>>>> 866a1c0 (...)
                    $player->pitch = $moveData['pitch'];
                    $player->headYaw = $moveData['headYaw'];

                    $to = $player->getLocation();

                    if (self::$server !== null) {
<<<<<<< HEAD
                        self::$server->dispatch(new \watermossmc\event\PlayerMoveEvent($player, $from, $to));
                    }
                    return;
            }

        } catch (Throwable $e) {
=======
                        self::$server->dispatch(new PlayerMoveEvent($player, $from, $to));
                    }
                    return;

                case ProtocolInfo::TEXT_PACKET: // Text (0x09)
                    Logger::debug("[0x09] Text packet received.");
                    $type = Binary::readByte($packet, $o);
                    $needsTranslation = Binary::readBool($packet, $o);

                    $sourceName = "";
                    if (\in_array($type, [protocol\Text::TYPE_CHAT, protocol\Text::TYPE_WHISPER, protocol\Text::TYPE_ANNOUNCEMENT], true)) {
                        $sourceName = Binary::readString($packet, $o);
                        Binary::readString($packet, $o); // Platform ID
                    }

                    $message = Binary::readString($packet, $o);
                    Logger::info("[Chat] {$sourceName}: {$message}");

                    // Skip the remaining fields for now
                    Binary::readVarInt($packet, $o);
                    Binary::readString($packet, $o);
                    Binary::readString($packet, $o);
                    return;

                case ProtocolInfo::COMMAND_REQUEST_PACKET: // CommandRequest (0x4d)
                    Logger::debug("[0x4D] CommandRequest received.");

                    $player = PlayerManager::get($session);
                    if ($player === null) {
                        return;
                    }

                    try {
                        $data = protocol\CommandRequest::read($packet, $o);
                        $command = $data['command'];
                        Logger::info("Player {$player->getUsername()} executed command: {$command}");

                        Server::getInstance()?->dispatchCommand($player, $command);
                    } catch (Throwable $e) {
                        Logger::error("Failed to handle CommandRequest: {$e->getMessage()}");
                    }
                    return;
            }
        } catch (Throwable $e) {


>>>>>>> 866a1c0 (...)
            Logger::error("Packet handling error [PID: 0x" . ($pidHex ?? '??') . "]: {$e->getMessage()}");
            Logger::debug($e->getTraceAsString());
            Disconnect::send($session, $socket, "Internal Server Error");
            RakNet::flush($session, $socket);
        }
    }

<<<<<<< HEAD
    private static function startPlay(Session $s, Socket $sock): void
    {
        Logger::debug("Initializing world sequence...");
=======
    public static function syncPlayers(): void
    {
        $players = PlayerManager::all();
        foreach ($players as $player) {
            $socket = $player->session->getSocket();
            if ($socket === null) {
                continue;
            }

            foreach ($players as $other) {
                if ($player === $other) {
                    continue;
                }
                protocol\MoveActorAbsolute::send($player->session, $socket, $other);
            }
        }
    }

    private static function startPlay(Session $s, Socket $sock): void
    {
        Logger::info("Starting game sequence for " . $s->getPlayerName());
>>>>>>> 866a1c0 (...)

        $world = self::getWorld();

        if (self::$server === null) {
<<<<<<< HEAD
            throw new \RuntimeException("Server not initialized");
        }
        $player = PlayerManager::add($s, $s->getPlayerName(), self::$server);
=======
            throw new RuntimeException("Server not initialized");
        }
        $player = PlayerManager::add($s, $s->getPlayerName(), self::$server);

        $world->getEntityManager()->addEntity($player);

>>>>>>> 866a1c0 (...)
        self::$server->dispatch(new PlayerJoinEvent(self::$server, $player));

        $spawn = $world->getSpawnPosition();
        $playerSpawn = $world->getPlayerSpawnPosition();

        $s->setMcpeState(Session::MC_PRESPAWN);
        $s->setPosition($playerSpawn['x'], $playerSpawn['y'], $playerSpawn['z']);

<<<<<<< HEAD
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
=======
        /**Logger::debug("[Sequence] Sending VoxelShapes...");
        VoxelShapes::send($s, $sock, [], [], 0);*/

        Logger::debug("[Sequence] Sending StartGame...");
        StartGame::send($s, $sock, $world, $player);

        /**Logger::debug("[Sequence] Sending ItemRegistry...");
        ItemRegistry::send($s, $sock);

        Logger::debug("[Sequence] Sending AvailableActorIdentifiers...");
        AvailableActorIdentifiers::send($s, $sock);

        Logger::debug("[Sequence] Sending BiomeDefinitionList...");
        BiomeDefinitionList::send($s, $sock);

        Logger::debug("[Sequence] Sending SetTime...");
        SetTime::send($s, $sock, $world->getDayTime());

        Logger::debug("[Sequence] Sending UpdateAttributes...");
        UpdateAttributes::send($player, $sock);

        Logger::debug("[Sequence] Sending AvailableCommands...");
        AvailableCommands::send($s, $sock);

        Logger::debug("[Sequence] Sending UpdateAbilities...");
        UpdateAbilities::send($s, $sock);

        Logger::debug("[Sequence] Sending UpdateAdventureSettings...");
        UpdateAdventureSettings::send($s, $sock, false, false, false, true, true);

        Logger::debug("[Sequence] Sending MobEffects...");
        foreach ($player->getEffects() as $id => $effect) {
            MobEffect::add($s, $sock, $id, $effect['amplifier'], $effect['particles'], $effect['duration'], $effect['ambient']);
        }

        Logger::debug("[Sequence] Sending SetActorData...");
        SetActorData::send($player, $s, $sock);

        Logger::debug("[Sequence] Sending InventoryContent...");
        InventoryContent::send($s, $sock, InventoryContent::WINDOW_INVENTORY, $player->inventory->getWindowItems(InventoryContent::WINDOW_INVENTORY));
        InventoryContent::send($s, $sock, InventoryContent::WINDOW_ARMOR, $player->inventory->getWindowItems(InventoryContent::WINDOW_ARMOR));

        Logger::debug("[Sequence] Sending PlayerHotbar...");
        PlayerHotbar::send($s, $sock, $player->inventory->getSelectedSlot());

        Logger::debug("[Sequence] Sending CreativeContent...");
        CreativeContent::sendEmpty($s, $sock);

        Logger::debug("[Sequence] Sending CraftingData...");
        CraftingData::sendEmpty($s, $sock);

        Logger::debug("[Sequence] Sending PlayerList...");
        PlayerList::sendAdd($s, $sock);

        foreach (PlayerManager::all() as $onlinePlayer) {
            if ($onlinePlayer === $player) {
                continue;
            }

            AddPlayer::send($s, $sock, $onlinePlayer);

            $otherSocket = $onlinePlayer->session->getSocket();
            if ($otherSocket !== null) {
                AddPlayer::send($onlinePlayer->session, $otherSocket, $player);
            }
        }*/

>>>>>>> 866a1c0 (...)
        RakNet::flush($s, $sock);

        $s->setWaitingRequestChunkRadiusAck(true);

<<<<<<< HEAD
        Logger::debug("Waiting for chunk radius request");
=======
        Logger::info("Game sequence sent. Waiting for RequestChunkRadius from client...");
>>>>>>> 866a1c0 (...)
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
<<<<<<< HEAD
            throw new \RuntimeException("Failed to encode header");
=======
            throw new RuntimeException("Failed to encode header");
>>>>>>> 866a1c0 (...)
        }

        $payload = json_encode([
            "salt" => base64_encode($salt),
        ], \JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
<<<<<<< HEAD
            throw new \RuntimeException("Failed to encode payload");
=======
            throw new RuntimeException("Failed to encode payload");
>>>>>>> 866a1c0 (...)
        }

        $h = $toUrlSafe($header);
        $p = $toUrlSafe($payload);
        $dataToSign = "$h.$p";

        $sigDer = '';
        if (!openssl_sign($dataToSign, $sigDer, $priv, \OPENSSL_ALGO_SHA384)) {
<<<<<<< HEAD
            throw new \RuntimeException("OpenSSL sign failed");
=======
            throw new RuntimeException("OpenSSL sign failed");
>>>>>>> 866a1c0 (...)
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

<<<<<<< HEAD
=======
        // The Bedrock protocol often requires sending a "cache enabled" boolean
        // and a list of blob hashes before the chunks themselves.
        // However, LevelChunk::send in this codebase handles the individual chunk.
        // We need to ensure the chunk encoding itself respects the cache setting.

>>>>>>> 866a1c0 (...)
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
