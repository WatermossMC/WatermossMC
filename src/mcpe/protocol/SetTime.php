<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class SetTime extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $p = Binary::writeInt(6000);
        self::sendBatch(ProtocolInfo::SET_TIME_PACKET, $p, $s, $sock);
    }
}
