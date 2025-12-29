<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

final class LevelChunk extends Packet
{
    public static function send(
        Session $s,
        Socket $sock,
        int $chunkX,
        int $chunkZ,
        string $chunkData
    ): void {
        $p = Binary::writeInt($chunkX);
        $p .= Binary::writeInt($chunkZ);
        $p .= Binary::writeVarInt(24);
        $p .= Binary::writeBool(false);

        $p .= Binary::writeVarInt(\strlen($chunkData));
        $p .= $chunkData;

        $p .= self::writeBiomeData();
        $p .= Binary::writeVarInt(0);

        self::sendBatch(0x3A, $p, $s, $sock);
    }

    private static function writeBiomeData(): string
    {
        $out = '';

        $palette = [1];
        $bits = 1;

        $out .= Binary::writeByte($bits);

        $words = intdiv(256 * $bits + 31, 32);
        $out .= str_repeat("\x00\x00\x00\x00", $words);

        $out .= Binary::writeVarInt(\count($palette));
        foreach ($palette as $biomeId) {
            $out .= Binary::writeVarInt($biomeId);
        }

        return $out;
    }
}
