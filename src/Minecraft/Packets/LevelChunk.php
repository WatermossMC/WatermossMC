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
        $p = Binary::writeByte(0x3A);      // LevelChunk
        $p .= Binary::writeInt($chunkX);    // chunkX
        $p .= Binary::writeInt($chunkZ);    // chunkZ
        $p .= Binary::writeVarInt(24);      // subchunk count
        $p .= Binary::writeBool(false);     // cache enabled

        $p .= Binary::writeVarInt(\strlen($chunkData));
        $p .= $chunkData;

        $p .= self::writeBiomeData();
        $p .= Binary::writeVarInt(0);       // no block entities

        self::sendBatch($p, $s, $sock);
    }

    private static function writeBiomeData(): string
    {
        $out = '';

        $palette = [1]; // plains
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
