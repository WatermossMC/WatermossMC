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

namespace watermossmc\util;

use watermossmc\world\World;

final class Location
{
    public function __construct(public readonly World $world, public float $x, public float $y, public float $z, public float $yaw = 0, public float $pitch = 0) {}

    /**
     * @return array{x: float, y: float, z: float, yaw: float, pitch: float}
     */
    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z, 'yaw' => $this->yaw, 'pitch' => $this->pitch];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public function blockToInteger(): array
    {
        return [(int) floor($this->x), (int) floor($this->y), (int) floor($this->z)];
    }

    public function __toString(): string
    {
        return \sprintf('%s (%.2f, %.2f, %.2f)', $this->world->getName(), $this->x, $this->y, $this->z);
    }
}
