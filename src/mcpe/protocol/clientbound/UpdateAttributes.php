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

namespace watermossmc\mcpe\protocol\clientbound;

use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\ProtocolInfo;

use function count;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;

final class UpdateAttributes extends Packet
{
    public static function send(\watermossmc\player\Player $player, Socket $sock): void
    {
        $s = $player->session;
        $runtimeId = $s->getRuntimeId();
        $attributes = $player->getAttributeMap()->getAll();
        $tick = 0;
        $payload = '';
        $payload .= Binary::writeVarLong($runtimeId);
        $payload .= Binary::writeVarInt(\count($attributes));
        foreach ($attributes as $attr) {
            $payload .= Binary::writeFloat($attr->getMin());
            $payload .= Binary::writeFloat($attr->getMax());
            $payload .= Binary::writeFloat($attr->getValue());
            $payload .= Binary::writeFloat($attr->getDefault());
            $payload .= McpeBinary::writeString($attr->getId());
            $payload .= McpeBinary::writeSignedVarInt(0);
            // modifier count
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
        return [['name' => 'minecraft:health', 'min' => 0.0, 'max' => 20.0, 'current' => 20.0, 'default' => 20.0], ['name' => 'minecraft:follow_range', 'min' => 0.0, 'max' => 2048.0, 'current' => 32.0, 'default' => 32.0], ['name' => 'minecraft:knockback_resistance', 'min' => 0.0, 'max' => 1.0, 'current' => 0.0, 'default' => 0.0], ['name' => 'minecraft:movement', 'min' => 0.0, 'max' => 3.4028235E+38, 'current' => 0.1, 'default' => 0.1], ['name' => 'minecraft:attack_damage', 'min' => 0.0, 'max' => 3.4028235E+38, 'current' => 1.0, 'default' => 1.0], ['name' => 'minecraft:absorption', 'min' => 0.0, 'max' => 3.4028235E+38, 'current' => 0.0, 'default' => 0.0], ['name' => 'minecraft:luck', 'min' => -1024.0, 'max' => 1024.0, 'current' => 0.0, 'default' => 0.0], ['name' => 'minecraft:player.hunger', 'min' => 0.0, 'max' => 20.0, 'current' => 20.0, 'default' => 20.0], ['name' => 'minecraft:player.saturation', 'min' => 0.0, 'max' => 20.0, 'current' => 20.0, 'default' => 20.0], ['name' => 'minecraft:player.exhaustion', 'min' => 0.0, 'max' => 5.0, 'current' => 0.0, 'default' => 0.0], ['name' => 'minecraft:player.level', 'min' => 0.0, 'max' => 24791.0, 'current' => 0.0, 'default' => 0.0], ['name' => 'minecraft:player.experience', 'min' => 0.0, 'max' => 1.0, 'current' => 0.0, 'default' => 0.0]];
    }
}
