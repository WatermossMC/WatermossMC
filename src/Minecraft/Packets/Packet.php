<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Network\Session;

abstract class Packet
{
    protected static function sendBatch(string $payload, Session $s, Socket $sock): void
    {
        $batch = Binary::writeVarInt(1);
        $batch .= Binary::writeVarInt(\strlen($payload));
        $batch .= $payload;

        $useCompression = $s->shouldCompressOutbound()
            && $s->getMcpeState() >= Session::MC_LOGIN;

        if ($useCompression && $s->shouldCompressOutbound()) {
            $data = zlib_encode($batch, \ZLIB_ENCODING_DEFLATE);
            if ($data === false) {
                return;
            }
            $packet = "\xFE" . $data;
        } else {
            $packet = "\xFE" . $batch;
        }

        $sendSeq = $s->nextSendSeq();
        $reliableIndex = $s->nextReliableSeq();

        $frame = Binary::writeByte(0x84);
        $frame .= Binary::writeTriad($sendSeq);
        $frame .= Binary::writeByte(0x40);
        $frame .= Binary::writeShort(\strlen($packet) * 8);
        $frame .= Binary::writeTriad($reliableIndex);
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
    }
}
