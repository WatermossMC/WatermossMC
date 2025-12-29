<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class ResourcePacksInfo extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {

        $p = Binary::writeBool(false);


        $p .= Binary::writeBool(false);


        $p .= Binary::writeBool(false);


        $p .= Binary::writeBool(false);


        $p .= Binary::writeUUID('00000000-0000-0000-0000-000000000000');


        $p .= Binary::writeStringInt("");


        $p .= Binary::writeShort(0);

        self::sendBatch(0x06, $p, $s, $sock);
    }
}
