<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;

final class Disconnect extends Packet
{
    public static function send(Session $s, Socket $sock, string $reason = ''): void
    {
        $p = McpeBinary::writeString($reason);

        self::sendBatch(ProtocolInfo::DISCONNECT_PACKET, $p, $s, $sock);
    }
}
