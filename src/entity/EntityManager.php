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

use Throwable;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class EntityManager
{
    /** @var array<int, Entity> */
    private array $entities = [];

    public function __construct(
        private readonly World $world
    ) {}

    public function getWorld(): World
    {
        return $this->world;
    }

    public function addEntity(Entity $entity): void
    {
        $this->entities[$entity->getRuntimeId()] = $entity;
    }

    public function removeEntity(int $runtimeId): void
    {
        unset($this->entities[$runtimeId]);
    }

    public function getEntity(int $runtimeId): ?Entity
    {
        return $this->entities[$runtimeId] ?? null;
    }

    /**
     * @return array<int, Entity>
     */
    public function getAllEntities(): array
    {
        return $this->entities;
    }

    public function tick(): void
    {
        foreach ($this->entities as $entity) {
            try {
                $entity->tick();
            } catch (Throwable $e) {
                Logger::error("Error ticking entity {$entity->getRuntimeId()}: {$e->getMessage()}");
            }
        }
    }
}
