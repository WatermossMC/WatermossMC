<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Network\Session;

final class UpdateAttributes extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $runtimeId = $s->getRuntimeId();
        $attributes = self::getDefaultAttributes();
        $tick = 0;

        $payload = '';
        $payload .= Binary::writeVarLong($runtimeId);
        $payload .= Binary::writeVarInt(\count($attributes));

        foreach ($attributes as $attr) {
            $payload .= Binary::writeFloat($attr['min']);
            $payload .= Binary::writeFloat($attr['max']);
            $payload .= Binary::writeFloat($attr['current']);
            $payload .= Binary::writeFloat($attr['default']);
            $payload .= McpeBinary::writeString($attr['name']);
            $payload .= McpeBinary::writeSignedVarInt(0); // modifier count
        }

        $payload .= Binary::writeVarLong($tick);

        self::sendBatch(ProtocolInfo::UPDATE_ATTRIBUTES_PACKET, $payload, $s, $sock);
    }

    /**
     * Returns the standard player attribute set with sane defaults.
     * Adjust 'current' per-player if you track health/hunger/etc separately.
     *
     * @return array<int, array{name: string, min: float, max: float, current: float, default: float}>
     */
    private static function getDefaultAttributes(): array
    {
        return [
            ['name' => 'minecraft:health',           'min' => 0.0,   'max' => 20.0,   'current' => 20.0,  'default' => 20.0],
            ['name' => 'minecraft:follow_range',      'min' => 0.0,   'max' => 2048.0, 'current' => 32.0,  'default' => 32.0],
            ['name' => 'minecraft:knockback_resistance', 'min' => 0.0, 'max' => 1.0,   'current' => 0.0,   'default' => 0.0],
            ['name' => 'minecraft:movement',          'min' => 0.0,   'max' => 3.4028235E38, 'current' => 0.1, 'default' => 0.1],
            ['name' => 'minecraft:attack_damage',     'min' => 0.0,   'max' => 3.4028235E38, 'current' => 1.0, 'default' => 1.0],
            ['name' => 'minecraft:absorption',        'min' => 0.0,   'max' => 3.4028235E38, 'current' => 0.0, 'default' => 0.0],
            ['name' => 'minecraft:luck',              'min' => -1024.0, 'max' => 1024.0, 'current' => 0.0, 'default' => 0.0],
            ['name' => 'minecraft:player.hunger',     'min' => 0.0,   'max' => 20.0,   'current' => 20.0,  'default' => 20.0],
            ['name' => 'minecraft:player.saturation', 'min' => 0.0,   'max' => 20.0,   'current' => 20.0,  'default' => 20.0],
            ['name' => 'minecraft:player.exhaustion', 'min' => 0.0,   'max' => 5.0,    'current' => 0.0,   'default' => 0.0],
            ['name' => 'minecraft:player.level',      'min' => 0.0,   'max' => 24791.0,'current' => 0.0,   'default' => 0.0],
            ['name' => 'minecraft:player.experience', 'min' => 0.0,   'max' => 1.0,    'current' => 0.0,   'default' => 0.0],
        ];
    }
}
