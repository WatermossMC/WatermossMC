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
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\network\Session;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;

final class LevelChunk extends Packet
{
    private const MAX_BLOB_HASHES = 64;

    public static function send(
        Session $s,
        Socket $sock,
        int $chunkX,
        int $chunkZ,
        string $chunkData,
        int $subChunkCount,
        int $dimensionId = 0
    ): void {
        $p = McpeBinary::writeSignedVarInt($chunkX);
        $p .= McpeBinary::writeSignedVarInt($chunkZ);

        // Dimension ID
        $p .= McpeBinary::writeSignedVarInt($dimensionId);

        // SubChunk count
        $p .= McpeBinary::writeUnsignedVarInt($subChunkCount);

        $clientRequestSubChunkLimit = null;

        $p .= Binary::writeBool($clientRequestSubChunkLimit !== null);

        if ($clientRequestSubChunkLimit !== null) {
            $p .= McpeBinary::writeSignedVarInt($clientRequestSubChunkLimit);
        }

        // Cache enabled
        $cacheEnabled = $s->isCacheEnabled();
        $p .= Binary::writeBool($cacheEnabled);

        // Used blob hashes
        $usedBlobHashes = [];

        $p .= McpeBinary::writeUnsignedVarInt(0);

        // Extra payload
        $p .= McpeBinary::writeString($chunkData);

        self::sendBatch(
            ProtocolInfo::LEVEL_CHUNK_PACKET,
            $p,
            $s,
            $sock
        );
    }
}
