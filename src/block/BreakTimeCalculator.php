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

namespace watermossmc\block;

/**
 * Computes how long (in seconds) it takes to break a block by hand, following vanilla's base
 * formula. Tool-specific speed multipliers and enchantments aren't modelled yet since the item
 * catalog doesn't have mining tools beyond a sword; this only implements the hand-breaking case,
 * which is what every block resolves to until tool effectiveness is added.
 */
final class BreakTimeCalculator
{
    /**
     * Vanilla's hand-breaking time: hardness * 1.5 seconds, or instantly (0) for a hardness of 0.
     * Blocks with a negative hardness (unbreakable, e.g. bedrock) never finish breaking.
     */
    public static function calculate(Block $block): float
    {
        $hardness = $block->getHardness();

        if ($hardness < 0.0) {
            return \PHP_FLOAT_MAX;
        }
        if ($hardness === 0.0) {
            return 0.0;
        }

        return $hardness * 1.5;
    }
}
