<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;
use watermossmc\player\Player;

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

        self::sendBatch(ProtocolInfo::MOVE_PLAYER_PACKET, $payload, $s, $sock);
    }
}
