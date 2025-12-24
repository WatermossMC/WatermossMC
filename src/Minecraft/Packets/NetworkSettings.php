<?php


declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class NetworkSettings extends Packet
{
    public const COMPRESS_NOTHING = 0;
    public const COMPRESS_EVERYTHING = 1;

    public static function send(Session $s, Socket $sock): void
    {
        $p = Binary::writeByte(0x8F);
        $p .= Binary::writeLShort(1); // compress everything ≥ 1 byte
        $p .= Binary::writeLShort(0);
        $p .= Binary::writeBool(false);
        $p .= Binary::writeByte(0);
        $p .= Binary::writeLFloat(0.0);

        self::sendBatch($p, $s, $sock);
        $s->markNetworkSettingsSent();
    }
}
