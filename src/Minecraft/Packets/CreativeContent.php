<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

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