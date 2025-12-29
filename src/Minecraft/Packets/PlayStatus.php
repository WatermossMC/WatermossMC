<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class PlayStatus extends Packet
{
    public static function sendSuccess(Session $s, Socket $sock): void
    {
        $payload = Binary::writeInt(0);
        self::send($payload, $s, $sock);
    }

    public static function sendPlayerSpawn(Session $s, Socket $sock): void
    {
        $payload = Binary::writeInt(3);
        self::send($payload, $s, $sock);
    }

    private static function send(string $payload, Session $s, Socket $sock): void
    {
        self::sendBatch(0x02, $payload, $s, $sock);
    }
}
