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

declare (strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\data\BiomeDefinitions;
use watermossmc\mcpe\cache\StaticPacketCache;
use watermossmc\mcpe\network\Session;

final class BiomeDefinitionList extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $payload = StaticPacketCache::getInstance()->get('biome_definition_list', function () {
            /** @var array<string, int> $lookup */
            $lookup = [];
            $strings = [];
            $intern = static function (string $str) use (&$lookup, &$strings): int {
                if (isset($lookup[$str])) {
                    return $lookup[$str];
                }
                $idx = \count($strings);
                $lookup[$str] = $idx;
                $strings[] = $str;
                return $idx;
            };
            $biomes = BiomeDefinitions::getAll();
            $definitions = [];
            foreach ($biomes as $biome) {
                $tagIndexes = null;
                if (!empty($biome['tags'])) {
                    $tagIndexes = array_map($intern, $biome['tags']);
                }
                $definitions[] = ['nameIndex' => $intern($biome['nameString']), 'packetId' => $biome['packetId'], 'temperature' => $biome['temperature'], 'downfall' => $biome['downfall'], 'foliageSnow' => $biome['foliageSnow'], 'depth' => $biome['depth'], 'scale' => $biome['scale'], 'mapWaterColor' => $biome['mapWaterColor'], 'hasRain' => $biome['hasRain'], 'tagIndexes' => $tagIndexes];
            }
            $res = '';
            $res .= Binary::writeVarInt(\count($definitions));
            foreach ($definitions as $def) {
                $res .= Binary::writeLShort($def['nameIndex']);
                $res .= Binary::writeLShort($def['packetId']);
                $res .= Binary::writeLFloat($def['temperature']);
                $res .= Binary::writeLFloat($def['downfall']);
                $res .= Binary::writeLFloat($def['foliageSnow']);
                $res .= Binary::writeLFloat($def['depth']);
                $res .= Binary::writeLFloat($def['scale']);
                $res .= Binary::writeLInt($def['mapWaterColor']);
                $res .= Binary::writeBool($def['hasRain']);
                if ($def['tagIndexes'] === null) {
                    $res .= Binary::writeBool(false);
                } else {
                    $res .= Binary::writeBool(true);
                    $res .= Binary::writeVarInt(\count($def['tagIndexes']));
                    foreach ($def['tagIndexes'] as $tagIdx) {
                        $res .= Binary::writeLShort($tagIdx);
                    }
                }
                $res .= Binary::writeBool(false);
            }
            $res .= Binary::writeVarInt(\count($strings));
            foreach ($strings as $str) {
                $res .= McpeBinary::writeString($str);
            }
            return $res;
        });
        self::sendBatch(ProtocolInfo::BIOME_DEFINITION_LIST_PACKET, $payload, $s, $sock);
    }
}
