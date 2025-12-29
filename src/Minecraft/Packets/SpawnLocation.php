<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class SpawnPosition extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $p = Binary::writeInt(0);
        $p .= Binary::writeInt(0);
        $p .= Binary::writeInt(64);
        $p .= Binary::writeInt(0);
        self::sendBatch(0x44, $p, $s, $sock);
    }
}
