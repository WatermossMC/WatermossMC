<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class Disconnect extends Packet
{
    public static function send(Session $s, Socket $sock, string $reason = ''): void
    {
        $p = Binary::writeString($reason);

        self::sendBatch(0x05, $p, $s, $sock);
    }
}
