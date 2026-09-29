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

use Ramsey\Uuid\Uuid;
use Throwable;
use watermossmc\inventory\ItemStack;
use watermossmc\network\mcpe\protocol\clientbound\AddItemActor;
use watermossmc\network\mcpe\protocol\clientbound\InventoryContent;
use watermossmc\network\mcpe\protocol\clientbound\RemoveActor;
use watermossmc\player\Player;
use watermossmc\player\PlayerManager;
use watermossmc\util\Logger;
use watermossmc\world\World;

final class EntityManager
{
    private const FIRST_ENTITY_RUNTIME_ID = 1_000_000;

    /** @var array<int, Entity> */
    private array $entities = [];

    private int $nextEntityRuntimeId = self::FIRST_ENTITY_RUNTIME_ID;

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

    public function spawnItem(ItemStack $stack, float $x, float $y, float $z, float $motionX = 0.0, float $motionY = 0.2, float $motionZ = 0.0): ItemEntity
    {
        $entity = new ItemEntity($this->nextEntityRuntimeId++, Uuid::uuid4()->toString(), $this->world, $stack);
        $entity->setPosition($x, $y, $z);
        $this->addEntity($entity);

        foreach (PlayerManager::all() as $viewer) {
            $socket = $viewer->session->getSocket();
            if ($socket !== null && $viewer->getWorld() === $this->world) {
                AddItemActor::send($viewer->session, $socket, $entity->getRuntimeId(), $stack, $x, $y, $z, $motionX, $motionY, $motionZ);
            }
        }

        return $entity;
    }

    public function tick(): void
    {
        foreach ($this->entities as $entity) {
            try {
                $entity->tick();
            } catch (Throwable $e) {
                Logger::error("Error ticking entity {$entity->getRuntimeId()}: {$e->getMessage()}");
            }

            if ($entity instanceof ItemEntity) {
                $this->tickItem($entity);
            }
        }
    }

    private function tickItem(ItemEntity $item): void
    {
        if ($item->isExpired()) {
            $this->despawn($item);
            return;
        }
        if (!$item->canBePickedUp()) {
            return;
        }

        foreach (PlayerManager::all() as $player) {
            if ($player->getWorld() !== $this->world || !$item->isNear($player->x, $player->y, $player->z)) {
                continue;
            }
            $this->pickUp($item, $player);
            return;
        }
    }

    private function pickUp(ItemEntity $item, Player $player): void
    {
        $stack = $item->getItemStack();
        $left = $player->getInventory()->addItem($stack);
        if ($left === $stack->count) {
            return;
        }

        $socket = $player->session->getSocket();
        if ($socket !== null) {
            InventoryContent::sendForInventory($player->session, $socket, InventoryContent::WINDOW_INVENTORY, $player->getInventory());
        }

        if ($left > 0) {
            $stack->count = $left;
            return;
        }
        $this->despawn($item);
    }

    private function despawn(ItemEntity $item): void
    {
        $this->removeEntity($item->getRuntimeId());
        foreach (PlayerManager::all() as $viewer) {
            $socket = $viewer->session->getSocket();
            if ($socket !== null && $viewer->getWorld() === $this->world) {
                RemoveActor::send($viewer->session, $socket, $item->getRuntimeId());
            }
        }
    }
}
