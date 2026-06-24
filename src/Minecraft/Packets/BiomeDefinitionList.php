<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Minecraft\Data\BiomeDefinitions;
use WatermossMC\Network\Session;

final class BiomeDefinitionList extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        /** @var array<string, int> $lookup */
        $lookup  = [];
        $strings = [];

        $intern = static function (string $str) use (&$lookup, &$strings): int {
            if (isset($lookup[$str])) {
                return $lookup[$str];
            }
            $idx         = \count($strings);
            $lookup[$str] = $idx;
            $strings[]   = $str;
            return $idx;
        };

        $biomes = BiomeDefinitions::getAll();

        $definitions = [];
        foreach ($biomes as $biome) {
            $tagIndexes = null;
            if (!empty($biome['tags'])) {
                $tagIndexes = \array_map($intern, $biome['tags']);
            }

            $definitions[] = [
                'nameIndex'      => $intern($biome['nameString']),
                'packetId'       => $biome['packetId'], 
                'temperature'    => $biome['temperature'],
                'downfall'       => $biome['downfall'],
                'foliageSnow'    => $biome['foliageSnow'],
                'depth'          => $biome['depth'],
                'scale'          => $biome['scale'],
                'mapWaterColor'  => $biome['mapWaterColor'],
                'hasRain'        => $biome['hasRain'],
                'tagIndexes'     => $tagIndexes,
            ];
        }

        $payload = '';

        $payload .= Binary::writeVarInt(\count($definitions));
        foreach ($definitions as $def) {
            $payload .= Binary::writeLShort($def['nameIndex']);
            $payload .= Binary::writeLShort($def['packetId']);
            $payload .= Binary::writeLFloat($def['temperature']);
            $payload .= Binary::writeLFloat($def['downfall']);
            $payload .= Binary::writeLFloat($def['foliageSnow']);
            $payload .= Binary::writeLFloat($def['depth']);
            $payload .= Binary::writeLFloat($def['scale']);
            $payload .= Binary::writeLInt($def['mapWaterColor']);
            $payload .= Binary::writeBool($def['hasRain']);

            if ($def['tagIndexes'] === null) {
                $payload .= Binary::writeBool(false);
            } else {
                $payload .= Binary::writeBool(true);
                $payload .= Binary::writeVarInt(\count($def['tagIndexes']));
                foreach ($def['tagIndexes'] as $tagIdx) {
                    $payload .= Binary::writeLShort($tagIdx);
                }
            }

            $payload .= Binary::writeBool(false); 
        }

        $payload .= Binary::writeVarInt(\count($strings));
        foreach ($strings as $str) {
            $payload .= McpeBinary::writeString($str);
        }

        self::sendBatch(ProtocolInfo::BIOME_DEFINITION_LIST_PACKET, $payload, $s, $sock);
    }
}
