<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class SetTime extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $p = Binary::writeInt(6000);
        self::sendBatch(0x0A, $p, $s, $sock);
    }
}
