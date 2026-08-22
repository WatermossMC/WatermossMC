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

namespace watermossmc\inventory;

use OutOfBoundsException;
use watermossmc\player\Player;

/**
 * Manages the inventory of a player with robust slot handling.
 */
final class PlayerInventory
{
    public const MAX_SIZE = 41;
    public const SLOT_INVENTORY_OFFSET = 9;
    public const SLOT_HOTBAR_OFFSET = 36;

    /** @var array<int, ItemStack|null> */
    private array $slots = [];

    private int $selectedSlot = 0;

    public function __construct()
    {
        $this->slots = array_fill(0, self::MAX_SIZE, null);
    }

    public function setItem(int $slot, ?ItemStack $item): void
    {
        if ($slot < 0 || $slot >= self::MAX_SIZE) {
            throw new OutOfBoundsException("Inventory slot out of bounds: {$slot}");
        }
        $this->slots[$slot] = $item;
    }

    public function getItem(int $slot): ?ItemStack
    {
        if ($slot < 0 || $slot >= self::MAX_SIZE) {
            return null;
        }
        return $this->slots[$slot] ?? null;
    }

    public function addItem(ItemStack $item): bool
    {
        // Try stacking first
        for ($i = 0; $i < 36; $i++) {
            $existing = $this->slots[$i];
            if ($existing !== null && $existing->getNumericId() === $item->getNumericId() && $existing->damage === $item->damage) {
                $existing->count += $item->count;
                return true;
            }
        }
        // Find empty slot
        for ($i = 0; $i < 36; $i++) {
            if ($this->slots[$i] === null) {
                $this->slots[$i] = clone $item;
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<int, ItemStack|null>
     */
    public function getWindowItems(int $windowId): array
    {
        return match ($windowId) {
            0 => \array_slice($this->slots, 0, 36), // General inventory + hotbar
            6 => \array_slice($this->slots, 36, 4),  // Armor
            119 => [$this->slots[40] ?? null],      // Offhand
            default => [],
        };
    }

    public function setSelectedSlot(int $slot): void
    {
        if ($slot >= 0 && $slot < 9) {
            $this->selectedSlot = $slot;
        }
    }

    public function getSelectedSlot(): int
    {
        return $this->selectedSlot;
    }

    public function getHolder(): ?Player
    {
        return null;
    }
}
