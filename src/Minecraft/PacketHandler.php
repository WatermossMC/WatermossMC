<?php


declare(strict_types=1);

namespace WatermossMC\Minecraft;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Crypto\Crypto;
use WatermossMC\Minecraft\Packets\{
    AddPlayer,
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
use WatermossMC\Network\Session;
use WatermossMC\Util\Logger;

final class PacketHandler
{
    private static ?World $world = null;

    public static function handleBatch(string $data, Session $session, Socket $socket): void
    {
        $offset = 0;
        $length = \strlen($data);

        Logger::debug("handleBatch len={$length} mcpestate={$session->getMcpeState()}");

        while ($offset < $length) {
            $pkLen = Binary::readVarInt($data, $offset);
            $pk = substr($data, $offset, $pkLen);
            $offset += $pkLen;

            if ($pk === '') {
                Logger::warning("Empty packet in batch");
                continue;
            }

            self::handlePacket($pk, $session, $socket);
        }
    }

    private static function handlePacket(string $packet, Session $session, Socket $socket): void
    {
        $pid = \ord($packet[0]);

        Logger::debug(sprintf(
            "IN packet pid=0x%02X len=%d state=%d",
            $pid,
            \strlen($packet),
            $session->getMcpeState()
        ));

        switch ($pid) {
            // CLIENT TO SERVER HANDSHAKE
            case 0x04:
                if (!\in_array($session->getMcpeState(), [
                    Session::MC_LOGIN,
                    Session::MC_HANDSHAKE,
                ], true)) {
                    Logger::warning("ClientToServerHandshake invalid state");
                    return;
                }
                $o = 1;

                $keys = Crypto::generateKeyPair();
                $session->setServerKeys($keys);

                $shared = Crypto::deriveSecret(
                    $keys['private'],
                    $session->getClientPublicKey()
                );

                $jwt = self::buildServerHandshakeJwt($keys['public'], $keys['private']);
                ServerToClientHandshake::send($session, $socket, $jwt);

                $session->setHandshakeDone();
                Logger::info("Handshake complete → RESOURCE");

                if ($session->isHandshakeDone()) {
                    ResourcePacksInfo::send($session, $socket);
                    $session->setMcpeState(Session::MC_RESOURCE);
                }

                [$key, $iv] = Crypto::deriveAes($shared);
                $session->setPendingEncryption($key, $iv);

                return;

                // LOGIN
            case 0x01:
                if ($session->getMcpeState() !== Session::MC_NETWORK) {
                    Logger::warning(
                        "Login invalid in state={$session->getMcpeState()})"
                    );
                    return;
                }
                $o = 1;
                $login = Login::read($packet, $o);
                $payload = $login['payload'];
                $identityPublicKey = $login['identityPublicKey'] ?? null;

                if (!\is_string($identityPublicKey) || $identityPublicKey === '') {
                    Logger::error("Login missing identityPublicKey");
                    Disconnect::send($session, $socket, "Invalid login key");
                    return;
                }

                $session->setClientPublicKey($identityPublicKey);

                $uuid = $payload['identity'] ?? null;
                $name = $payload['ThirdPartyName'] ?? 'Player';

                Logger::info("Login packet name={$name}");

                if (!\is_string($uuid)) {
                    Logger::error("Invalid login UUID");
                    Disconnect::send($session, $socket, 'Invalid login');
                    return;
                }

                $session->setLoginData($uuid, $name, null);
                $session->enableOutboundCompression(NetworkSettings::COMPRESS_EVERYTHING);
                $session->enableInboundCompression();
                $session->setMcpeState(Session::MC_LOGIN);

                Logger::info("Login OK uuid={$uuid}, waiting handshake");
                return;

                // RESOURCE PACK CLIENT RESPONSE
            case 0x08:
                if ($session->getMcpeState() !== Session::MC_RESOURCE) {
                    Logger::warning("ResourcePackClientResponse ignored (invalid state)");
                    return;
                }

                $o = 1;
                $rp = ResourcePackClientResponse::read($packet, $o);
                $status = $rp['status'];

                Logger::info("ResourcePackClientResponse status={$status}");

                if ($status === ResourcePackClientResponse::STATUS_REFUSED) {
                    Logger::error("Client refused resource packs");
                    Disconnect::send($session, $socket, 'Resource pack refused');
                    return;
                }

                if ($status === ResourcePackClientResponse::STATUS_HAVE_ALL_PACKS) {
                    Logger::debug("Client has all packs, sending ResourcePackStack");
                    ResourcePackStack::send($session, $socket);
                    return;
                }

                if ($status === ResourcePackClientResponse::STATUS_COMPLETED) {
                    Logger::info("Resource pack phase completed, starting play");
                    self::startPlay($session, $socket);
                    return;
                }

                Logger::warning("Unhandled resource pack status={$status}");
                return;

            case 0xC1:
                if ($session->getMcpeState() !== Session::MC_NONE) {
                    Logger::warning(
                        "RequestNetworkSettings must send before Login (state={$session->getMcpeState()})"
                    );
                    return;
                }

                RequestNetworkSettings::read($packet, $session, $socket);

                $session->setMcpeState(Session::MC_NETWORK);

                Logger::info("NetworkSettings sent, waiting login");
                return;
        }

        Logger::debug("Unhandled packet pid=0x" . dechex($pid));
    }

    private static function startPlay(Session $session, Socket $socket): void
    {
        if ($session->getMcpeState() === Session::MC_PLAY) {
            Logger::warning("startPlay called but already in PLAY state");
            return;
        }

        if (self::$world === null) {
            Logger::info("Creating world instance");
            self::$world = new World(
                'world',
                random_int(1, \PHP_INT_MAX)
            );
        }

        $session->setMcpeState(Session::MC_PLAY);
        PlayStatus::sendSuccess($session, $socket);
        $session->setPosition(0.0, 6.0, 0.0);

        Logger::info("Sending StartGame");
        StartGame::send($session, $socket);
        Logger::info("Sending PlayStatus (success)");
        PlayStatus::sendPlayerSpawn($session, $socket);

        Logger::debug("Sending SetTime & SpawnPosition");
        SetTime::send($session, $socket);
        SpawnPosition::send($session, $socket);

        Logger::info("Sending initial chunks");

        for ($cx = -1; $cx <= 1; $cx++) {
            for ($cz = -1; $cz <= 1; $cz++) {
                $chunk = self::$world->getChunk($cx, $cz);

                Logger::debug("Sending chunk {$cx}:{$cz}");

                LevelChunk::send(
                    $session,
                    $socket,
                    $cx,
                    $cz,
                    $chunk->encode()
                );
            }
        }

        $session->enterPlay();

        Logger::info("Player entered PLAY state");

        PlayerManager::add($session, $session->getUsername());
        PlayerList::send($session, $socket);
        AddPlayer::send($session, $socket);

        Logger::debug("PlayerList + AddPlayer sent");
    }

    private static function buildServerHandshakeJwt(string $serverPubPem, string $serverPrivPem): string
    {
        $header = [
            "alg" => "ES256",
            "typ" => "JWT",
        ];

        $payload = [
            "salt" => base64_encode(random_bytes(16)),
            "identityPublicKey" => $serverPubPem,
        ];

        $h = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $p = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

        openssl_sign("$h.$p", $sig, $serverPrivPem, \OPENSSL_ALGO_SHA256);

        $s = rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

        return "$h.$p.$s";
    }
}
