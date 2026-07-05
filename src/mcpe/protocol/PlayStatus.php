<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class PlayStatus extends Packet
{
    public static function sendSuccess(Session $s, Socket $sock): void
    {
        $payload = Binary::writeInt(0);
        self::send($payload, $s, $sock);
    }

    public static function sendFailedClient(Session $s, Socket $sock): void
    {
        $payload = Binary::writeInt(1);
        self::send($payload, $s, $sock);
    }

    public static function sendFailedServer(Session $s, Socket $sock): void
    {
        $payload = Binary::writeInt(2);
        self::send($payload, $s, $sock);
    }

    public static function sendPlayerSpawn(Session $s, Socket $sock): void
    {
        $payload = Binary::writeInt(3);
        self::send($payload, $s, $sock);
    }

    private static function send(string $payload, Session $s, Socket $sock): void
    {
        self::sendBatch(ProtocolInfo::PLAY_STATUS_PACKET, $payload, $s, $sock);
    }
}
