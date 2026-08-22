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

namespace watermossmc\block;

use watermossmc\player\Player;

abstract class Block
{
    public const AIR = 0;
    public const STONE = 1;
    public const GRASS_BLOCK = 2;
    public const DIRT = 3;
    public const BEDROCK = 4;

    public function __construct(public readonly int $id, public readonly string $name) {}

    public function getState(): BlockState
    {
        return new BlockState($this->name);
    }

    public function onPlace(int $x, int $y, int $z): void
    {
        // Default: do nothing
    }

    public function onBreak(int $x, int $y, int $z): void
    {
        // Default: do nothing
    }

    public function onInteract(int $x, int $y, int $z, Player $player): void
    {
        // Default: do nothing
    }
}
