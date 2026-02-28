<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Network\Session;

final class NetworkSettings extends Packet
{
    public const COMPRESS_NOTHING = 0;
    public const COMPRESS_EVERYTHING = 1;

    public static function send(Session $s, Socket $sock): void
    {
        $p = McpeBinary::writeLShort(self::COMPRESS_NOTHING);
        $p .= McpeBinary::writeLShort(0); // 0 = Zlib, 1 = Snappy, 255 = None
        $p .= McpeBinary::writeBool(false);
        $p .= McpeBinary::writeByte(0);
        $p .= McpeBinary::writeFloat(0.0);

        $sendSeq = self::sendBatch(0x8F, $p, $s, $sock);

        $s->markNetworkSettingsReliableSeq($sendSeq);
    }
}
