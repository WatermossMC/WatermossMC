<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;
use WatermossMC\Util\Logger;

final class RequestNetworkSettings extends Packet
{
    public static function read(string $payload, Session $s, Socket $sock): void
    {
        $offset = 1; // skip packetId (0xC1)

        $protocol = Binary::readVarInt($payload, $offset);

        Logger::debug("RequestNetworkSettings protocol={$protocol}");
    }
}
