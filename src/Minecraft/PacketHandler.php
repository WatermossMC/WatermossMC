<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft;

use Socket;
use Throwable;
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
        try {
            $data = $session->decodeInbound($data);
        } catch (Throwable $e) {
            Logger::error("Decode inbound failed: {$e->getMessage()}");
            return;
        }

        $offset = 0;
        $length = \strlen($data);
        $count = 0;

        while ($offset < $length) {
            try {
                $len = Binary::readVarInt($data, $offset);

                if ($len === 0) {
                    continue;
                }

                if ($len < 0 || $offset + $len > $length) {
                    Logger::warning("Corrupted packet length={$len}");
                    break;
                }

                $packet = substr($data, $offset, $len);
                $offset += $len;

                $count++;
                self::handlePacket($packet, $session, $socket);

            } catch (Throwable $e) {
                Logger::error("Batch packet error @{$offset}: {$e->getMessage()}");
                break;
            }
        }

        Logger::debug("Batch processed {$count} packets");
    }

    private static function handlePacket(string $packet, Session $session, Socket $socket): void
    {
        try {
            $o = 0;
            $pid = Binary::readVarInt($packet, $o);

            Logger::debug(
                "IN pid=0x" . dechex($pid) .
                " state=" . $session->getMcpeState()
            );

            switch ($pid) {



                case 0xC1:
                    if ($session->getMcpeState() !== Session::MC_NONE) {
                        Logger::warning("RequestNetworkSettings wrong state");
                        return;
                    }

                    RequestNetworkSettings::read($packet, $o, $session, $socket);
                    NetworkSettings::send($session, $socket);

                    $session->enableOutboundCompression(NetworkSettings::COMPRESS_EVERYTHING);
                    $session->enableInboundCompression();

                    $session->markNetworkSettingsSent();
                    $session->setMcpeState(Session::MC_NETWORK);
                    return;



                case 0x01:
                    if ($session->getMcpeState() !== Session::MC_NETWORK) {
                        Logger::warning("Login wrong state");
                        return;
                    }

                    $login = Login::read($packet, $o);
                    $payload = $login['payload'] ?? null;
                    $pubKey = $login['identityPublicKey'] ?? null;

                    if (!\is_array($payload) || !\is_string($pubKey)) {
                        Disconnect::send($session, $socket, "Invalid login payload");
                        return;
                    }

                    $uuid = $payload['identity'] ?? null;
                    $name = $payload['ThirdPartyName'] ?? 'Player';

                    if (!\is_string($uuid)) {
                        Disconnect::send($session, $socket, "Invalid UUID");
                        return;
                    }

                    $session->setClientPublicKey($pubKey);
                    $session->setLoginData($uuid, (string)$name, null);
                    $session->setMcpeState(Session::MC_LOGIN);
                    return;



                case 0x04:
                    if ($session->getMcpeState() !== Session::MC_LOGIN) {
                        Logger::warning("Handshake wrong state");
                        return;
                    }

                    $keys = Crypto::generateKeyPair();
                    $session->setServerKeys($keys);

                    $shared = Crypto::deriveSecret(
                        $keys['private'],
                        $session->getClientPublicKey()
                    );

                    $jwt = self::buildServerHandshakeJwt(
                        $keys['public'],
                        $keys['private']
                    );

                    ServerToClientHandshake::send($session, $socket, $jwt);

                    [$key, $iv] = Crypto::deriveAes($shared);
                    $session->setPendingEncryption($key, $iv);
                    $session->setWaitingHandshakeAck(true);

                    ResourcePacksInfo::send($session, $socket);
                    $session->setMcpeState(Session::MC_RESOURCE);
                    return;



                case 0x08:
                    if ($session->getMcpeState() !== Session::MC_RESOURCE) {
                        Logger::warning("Resource response wrong state");
                        return;
                    }

                    $rp = ResourcePackClientResponse::read($packet, $o);

                    match ($rp['status']) {
                        ResourcePackClientResponse::STATUS_HAVE_ALL_PACKS
                            => ResourcePackStack::send($session, $socket),

                        ResourcePackClientResponse::STATUS_COMPLETED
                            => self::startPlay($session, $socket),

                        default => null
                    };
                    return;
            }

        } catch (Throwable $e) {
            Logger::error("Packet handling error: {$e->getMessage()}");
        }
    }

    private static function startPlay(Session $s, Socket $sock): void
    {
        self::$world ??= new World('world', random_int(1, \PHP_INT_MAX));

        $s->setMcpeState(Session::MC_PLAY);

        PlayStatus::sendSuccess($s, $sock);
        StartGame::send($s, $sock);
        PlayStatus::sendPlayerSpawn($s, $sock);

        SetTime::send($s, $sock);
        SpawnPosition::send($s, $sock);

        for ($x = -1; $x <= 1; $x++) {
            for ($z = -1; $z <= 1; $z++) {
                LevelChunk::send(
                    $s,
                    $sock,
                    $x,
                    $z,
                    self::$world->getChunk($x, $z)->encode()
                );
            }
        }

        PlayerList::send($s, $sock);
        AddPlayer::send($s, $sock);

        Logger::info("Player entered PLAY");
    }

    private static function buildServerHandshakeJwt(string $pub, string $priv): string
    {
        $header = json_encode(["alg" => "ES256", "typ" => "JWT"], \JSON_THROW_ON_ERROR);
        $payload = json_encode([
            "salt" => base64_encode(random_bytes(16)),
            "identityPublicKey" => $pub,
        ], \JSON_THROW_ON_ERROR);

        $h = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $p = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        if (!openssl_sign("$h.$p", $sig, $priv, \OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException("JWT sign failed");
        }

        return "$h.$p." . rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');
    }
}
