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
        $oldHealth = $this->health;
        $this->health = max(0, min($this->maxHealth, $health));
        if ($oldHealth > 0 && $this->health <= 0) {
            $this->onDeath();
        }
    }

    public function isAlive(): bool
    {
        return $this->health > 0;
    }

    public function kill(): void
    {
        $this->setHealth(0);
    }

    public function onDeath(): void
    {
        // Default death logic
    }

    public function damage(float $amount): void
    {
        if (!$this->isAlive()) {
            return;
        }
        $this->setHealth($this->health - $amount);
    }

    public function tick(): void
    {
        parent::tick();
        // TODO: Additional living entity logic (e.g., health regeneration) can go here
    }
}
