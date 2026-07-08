<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;
use watermossmc\util\Logger;

abstract class Packet
{
    public static function sendBatch(
        int $packetId,
        string $payload,
        Session $s,
        Socket $sock
    ): int {
        $mcpePacket = Binary::writeVarInt($packetId);
        $mcpePacket .= $payload;

        $batch = Binary::writeVarInt(\strlen($mcpePacket));
        $batch .= $mcpePacket;

        $packet = $s->encodeOutbound($batch);

        // Consistent TX logging for all packets
        $pidHex = strtoupper(dechex($packetId));
        Logger::debug("[TX] Packet 0x{$pidHex} | Size: " . \strlen($packet) . " bytes | Session: {$s->getRuntimeId()}");

        if ($packetId === ProtocolInfo::SERVER_TO_CLIENT_HANDSHAKE_PACKET) {
            Logger::debug("[TX-Detail] Handshake inner hex: " . bin2hex($mcpePacket));
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

        $s->sendQueue[] = $frame;

        $s->storeReliable($sendSeq, $frame);
        return $sendSeq;
    }
}
