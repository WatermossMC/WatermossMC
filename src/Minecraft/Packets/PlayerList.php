<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Ramsey\Uuid\Uuid;
use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class PlayerList extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $uuid = Uuid::uuid4()->getBytes();

        $p = Binary::writeByte(0);
        $p .= Binary::writeInt(1);
        $p .= $uuid;
        $p .= Binary::writeLong(1);
        $p .= Binary::writeStringInt("Player");
        $p .= Binary::writeStringInt("");

        self::sendBatch(0x3F, $p, $s, $sock);
    }
}
