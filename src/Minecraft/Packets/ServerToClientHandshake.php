<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class ServerToClientHandshake extends Packet
{
    public static function send(Session $session, Socket $sock, string $jwt): void
    {
        $p = Binary::writeByte(0x03); // ServerToClientHandshake
        $p .= Binary::writeString($jwt);

        self::sendBatch($p, $session, $sock);
    }
}
