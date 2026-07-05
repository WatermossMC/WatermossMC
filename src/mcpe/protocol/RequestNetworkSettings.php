<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;
use watermossmc\util\Logger;

final class RequestNetworkSettings extends Packet
{
    public static function read(string $payload, int &$o, Session $s, Socket $sock): void
    {
        $protocol = Binary::readInt($payload, $o);

        Logger::debug("RequestNetworkSettings protocol={$protocol}");
    }
}
