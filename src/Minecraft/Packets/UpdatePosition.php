<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Minecraft\Player;
use WatermossMC\Network\Session;

final class UpdatePosition extends Packet
{
    public static function send(Player $p, Session $s, Socket $sock): void
    {
        $payload = Binary::writeLong(1);
        $payload .= pack("g", $p->x);
        $payload .= pack("g", $p->y);
        $payload .= pack("g", $p->z);
        $payload .= pack("g", $p->pitch);
        $payload .= pack("g", $p->yaw);
        $payload .= pack("g", 0.0);
        $payload .= Binary::writeBool($p->onGround);

        self::sendBatch(0x15, $payload, $s, $sock);
    }
}
