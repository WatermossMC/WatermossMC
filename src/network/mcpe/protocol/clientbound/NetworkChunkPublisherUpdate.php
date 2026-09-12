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

namespace watermossmc\network\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\types\ChunkPosition;
use watermossmc\network\Session;

final class NetworkChunkPublisherUpdate
{
    public static function send(
        Session $session,
        Socket $socket,
        int $x,
        int $y,
        int $z,
        int $radius,
        array $savedChunks = [],
    ): void {
        $payload = McpeBinary::writeSignedVarInt($x);
        $payload .= McpeBinary::writeSignedVarInt($y);
        $payload .= McpeBinary::writeSignedVarInt($z);

        $payload .= McpeBinary::writeUnsignedVarInt($radius);

        $payload .= McpeBinary::writeLInt(count($savedChunks));

        foreach ($savedChunks as $chunk) {
            if ($chunk instanceof ChunkPosition) {
                $payload .= $chunk->write();
            } else {
                $payload .= (new ChunkPosition(
                    $chunk[0],
                    $chunk[1],
                ))->write();
            }
        }

        Packet::sendBatch(
            ProtocolInfo::NETWORK_CHUNK_PUBLISHER_UPDATE_PACKET,
            $payload,
            $session,
            $socket,
        );
    }
}
