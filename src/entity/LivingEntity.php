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

use watermossmc\world\World;

abstract class LivingEntity extends Entity
{
    protected float $health = 20.0;

    protected float $maxHealth = 20.0;

    public function __construct(int $runtimeId, string $uuid, World $world)
    {
        parent::__construct($runtimeId, $uuid, $world);

        // Living entities usually have a name tag
        $this->getEntityData()->set(19, EntityData::TYPE_STRING, "Living Entity");
    }

    public function getHealth(): float
    {
        return $this->health;
    }

    public function setHealth(float $health): void
    {
        $this->health = max(0, min($this->maxHealth, $health));
    }

    public function damage(float $amount): void
    {
        $this->setHealth($this->health - $amount);
    }

    public function tick(): void
    {
        parent::tick();
        // Additional living entity logic (e.g., health regeneration) can go here
    }
}
