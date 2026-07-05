<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;

final class ServerToClientHandshake extends Packet
{
    public static function send(Session $session, Socket $sock, string $jwt): void
    {
        $p = McpeBinary::writeString($jwt);

        self::sendBatch(ProtocolInfo::SERVER_TO_CLIENT_HANDSHAKE_PACKET, $p, $session, $sock);
    }
}
