<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class CreativeContent extends Packet
{
    public static function sendEmpty(Session $s, Socket $sock): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt(0); // groups count
        $payload .= Binary::writeVarInt(0); // items count

        self::sendBatch(ProtocolInfo::CREATIVE_CONTENT_PACKET, $payload, $s, $sock);
    }
}
