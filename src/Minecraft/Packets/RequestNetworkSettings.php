<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;
use WatermossMC\Util\Logger;

final class RequestNetworkSettings extends Packet
{
    public static function read(string $payload, int &$o, Session $s, Socket $sock): void
    {
        $protocol = Binary::readInt($payload, $o);

        Logger::debug("RequestNetworkSettings protocol={$protocol}");
    }
}
