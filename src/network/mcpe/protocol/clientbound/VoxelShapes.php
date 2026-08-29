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

final class VoxelShapes extends Packet
{
    /**
     * @param array<int,array{
     *     cells:array{
     *         xSize:int,
     *         ySize:int,
     *         zSize:int,
     *         storage:string
     *     },
     *     xCoordinates:float[],
     *     yCoordinates:float[],
     *     zCoordinates:float[]
     * }> $shapes
     *
     * @param array<string,int> $nameMap
     */
    public static function send(Session $session, Socket $socket, array $shapes, array $nameMap, int $customShapeCount = 0): void
    {

        $payload = '';

        // Shapes
        $payload .= Binary::writeVarInt(\count($shapes));

        foreach ($shapes as $shape) {

            // SerializableCells
            $payload .= Binary::writeUInt8($shape['cells']['xSize']);
            $payload .= Binary::writeUInt8($shape['cells']['ySize']);
            $payload .= Binary::writeUInt8($shape['cells']['zSize']);

            $storage = $shape['cells']['storage'];

            $payload .= Binary::writeVarInt(\strlen($storage));

            $payload .= $storage;

            // X coordinates
            $payload .= Binary::writeVarInt(\count($shape['xCoordinates']));

            foreach ($shape['xCoordinates'] as $v) {
                $payload .= Binary::writeFloat($v);
            }

            // Y coordinates
            $payload .= Binary::writeVarInt(\count($shape['yCoordinates']));

            foreach ($shape['yCoordinates'] as $v) {
                $payload .= Binary::writeFloat($v);
            }

            // Z coordinates
            $payload .= Binary::writeVarInt(\count($shape['zCoordinates']));

            foreach ($shape['zCoordinates'] as $v) {
                $payload .= Binary::writeFloat($v);
            }
        }

        // NameMap
        $payload .= Binary::writeVarInt(\count($nameMap));

        foreach ($nameMap as $name => $id) {
            $payload .= McpeBinary::writeString($name);
            $payload .= Binary::writeLShort($id);
        }

        // customShapeCount
        $payload .= Binary::writeLShort($customShapeCount);

        self::sendBatch(
            ProtocolInfo::VOXEL_SHAPES_PACKET,
            $payload,
            $session,
            $socket
        );
    }
}
