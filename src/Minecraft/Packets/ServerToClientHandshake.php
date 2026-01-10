<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Network\Session;

final class ServerToClientHandshake extends Packet
{
    public static function send(Session $session, Socket $sock, string $jwt): void
    {
        $p = McpeBinary::writeString($jwt);

        self::sendBatch(0x03, $p, $session, $sock);
    }
}
