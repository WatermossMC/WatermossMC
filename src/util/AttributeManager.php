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

namespace watermossmc\util;

final class AttributeManager
{
    /** @var array<string, array{min: float, max: float, current: float, default: float}> */
    private static array $defaults = [
        'minecraft:health' => ['min' => 0.0,   'max' => 20.0,   'current' => 20.0,  'default' => 20.0],
        'minecraft:follow_range' => ['min' => 0.0,   'max' => 2048.0, 'current' => 32.0,  'default' => 32.0],
        'minecraft:knockback_resistance' => ['min' => 0.0, 'max' => 1.0,   'current' => 0.0,   'default' => 0.0],
        'minecraft:movement' => ['min' => 0.0,   'max' => 3.4028235E38, 'current' => 0.1, 'default' => 0.1],
        'minecraft:attack_damage' => ['min' => 0.0,   'max' => 3.4028235E38, 'current' => 1.0, 'default' => 1.0],
        'minecraft:absorption' => ['min' => 0.0,   'max' => 3.4028235E38, 'current' => 0.0, 'default' => 0.0],
        'minecraft:luck' => ['min' => -1024.0, 'max' => 1024.0, 'current' => 0.0, 'default' => 0.0],
        'minecraft:player.hunger' => ['min' => 0.0,   'max' => 20.0,   'current' => 20.0,  'default' => 20.0],
        'minecraft:player.saturation' => ['min' => 0.0,   'max' => 20.0,   'current' => 20.0,  'default' => 20.0],
        'minecraft:player.exhaustion' => ['min' => 0.0,   'max' => 5.0,    'current' => 0.0,   'default' => 0.0],
        'minecraft:player.level' => ['min' => 0.0,   'max' => 24791.0,'current' => 0.0,   'default' => 0.0],
        'minecraft:player.experience' => ['min' => 0.0,   'max' => 1.0,    'current' => 0.0,   'default' => 0.0],
    ];

    /** @return array<string, array{min: float, max: float, current: float, default: float}> */
    public static function getDefaultAttributes(): array
    {
        return self::$defaults;
    }

    public static function setDefaultAttribute(string $name, float $min, float $max, float $current, float $default): void
    {
        self::$defaults[$name] = [
            'min' => $min,
            'max' => $max,
            'current' => $current,
            'default' => $default,
        ];
    }
}
