<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Minecraft\Packets\Types\Experiments;
use WatermossMC\Network\Session;

final class ResourcePackStack extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $p = Binary::writeBool(false);
        $p .= Binary::writeVarInt(0);
        $p .= Binary::writeStringInt("1.21.124");

        $p .= Experiments::writeEmpty();


        $p .= Binary::writeBool(false);

        self::sendBatch(0x07, $p, $s, $sock);
    }
}
