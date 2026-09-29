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

namespace watermossmc\entity;

use watermossmc\inventory\ItemStack;
use watermossmc\world\World;

final class ItemEntity extends Entity
{
    public const PICKUP_DELAY_TICKS = 10;
    public const LIFETIME_TICKS = 6000;
    public const PICKUP_RADIUS = 1.5;

    private int $age = 0;

    public function __construct(
        int $runtimeId,
        string $uuid,
        World $world,
        private ItemStack $stack,
        private readonly int $pickupDelay = self::PICKUP_DELAY_TICKS
    ) {
        parent::__construct($runtimeId, $uuid, $world);
    }

    public function getItemStack(): ItemStack
    {
        return $this->stack;
    }

    public function canBePickedUp(): bool
    {
        return $this->age >= $this->pickupDelay;
    }

    public function isExpired(): bool
    {
        return $this->age >= self::LIFETIME_TICKS;
    }

    public function isNear(float $x, float $y, float $z): bool
    {
        $dx = $this->x - $x;
        $dy = $this->y - $y;
        $dz = $this->z - $z;

        return ($dx * $dx + $dy * $dy + $dz * $dz) <= self::PICKUP_RADIUS * self::PICKUP_RADIUS;
    }

    public function tick(): void
    {
        $this->age++;
        $this->tickPhysics();
    }
}
