<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class LevelChunk extends Packet
{
    public static function send(
        Session $s,
        Socket $sock,
        int $chunkX,
        int $chunkZ,
        string $chunkData,
        int $subChunkCount
    ): void {
        $p = Binary::writeVarInt($chunkX);
        $p .= Binary::writeVarInt($chunkZ);
        $p .= Binary::writeVarInt(0); // dimension ID
        $p .= Binary::writeVarInt($subChunkCount);
        $p .= Binary::writeBool(false); // cache enabled

        $p .= Binary::writeString($chunkData);
        $p .= Binary::writeString("");

        self::sendBatch(ProtocolInfo::LEVEL_CHUNK_PACKET, $p, $s, $sock);
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
