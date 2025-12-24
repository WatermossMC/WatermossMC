<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class StartGame extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $p = Binary::writeByte(0x0B);
        $p .= Binary::writeVarLong(1);
        $p .= Binary::writeVarLong(1);
        $p .= Binary::writeInt(1);
        $p .= pack("g", 0.0) . pack("g", 64.0) . pack("g", 0.0);
        $p .= pack("g", 0.0) . pack("g", 0.0);
        $p .= Binary::writeInt(1);
        $p .= Binary::writeInt(1);
        $p .= Binary::writeInt(0) . Binary::writeInt(64) . Binary::writeInt(0);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeInt(0);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeString("PHP World");
        $p .= Binary::writeInt(0);
        $p .= Binary::writeInt(0);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeString("php");
        $p .= Binary::writeString("PHP World");
        $p .= Binary::writeBool(false);
        $p .= Binary::writeBool(true);
        $p .= Binary::writeBool(true);

        self::sendBatch($p, $s, $sock);
    }
}
