<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

abstract class Packet
{
    protected static function sendBatch(
        int $packetId,
        string $payload,
        Session $s,
        Socket $sock
    ): int {
        $mcpePacket = Binary::writeVarInt($packetId);
        $mcpePacket .= $payload;

        $batch = Binary::writeVarInt(\strlen($mcpePacket));
        $batch .= $mcpePacket;

        if ($s->shouldCompressOutbound()) {
            $compressed = zlib_encode($batch, \ZLIB_ENCODING_RAW);
            if ($compressed === false) {
                throw new \RuntimeException("zlib_encode failed");
            }
            $packet = "\xFE" . $compressed;
        } else {
            $packet = "\xFE" . $batch;
        }

        $sendSeq = $s->nextSendSeq();
        $reliableIndex = $s->nextReliableSeq();
        $orderedIndex = $s->nextOrderedIndex();
        $orderChannel = 0;

        $frame = Binary::writeByte(0x80);
        $frame .= Binary::writeTriad($sendSeq);

        $frame .= Binary::writeByte(0x60);
        $frame .= Binary::writeShort(\strlen($packet) * 8);

        $frame .= Binary::writeTriad($reliableIndex);
        $frame .= Binary::writeTriad($orderedIndex);
        $frame .= Binary::writeByte($orderChannel);

        $frame .= $packet;

        socket_sendto(
            $sock,
            $frame,
            \strlen($frame),
            0,
            $s->address,
            $s->port
        );

        $s->storeReliable($sendSeq, $frame);
        return $sendSeq;
    }
}
