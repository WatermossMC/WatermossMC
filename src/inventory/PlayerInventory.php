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
 * Manages the inventory of a player.
 */
final class PlayerInventory
{
    /** @var array<int, ItemStack|null> */
    private array $slots = [];

    public function __construct()
    {
        // Initialize inventory with nulls (empty slots)
        // 0-35: General inventory
        // 36-39: Armor
        // 40: Offhand
        $this->slots = array_fill(0, 41, null);
    }

    public function setItem(int $slot, ?ItemStack $item): void
    {
        if ($slot < 0 || $slot >= 41) {
            throw new OutOfBoundsException("Inventory slot out of bounds");
        }
        $this->slots[$slot] = $item;
    }

    public function getItem(int $slot): ?ItemStack
    {
        return $this->slots[$slot] ?? null;
    }

    /**
     * Returns items for a specific window.
     * @return array<int, ItemStack|null>
     */
    public function getWindowItems(int $windowId): array
    {
        return match ($windowId) {
            0 => \array_slice($this->slots, 0, 36), // General
            6 => \array_slice($this->slots, 36, 4),  // Armor
            119 => [$this->slots[40]],            // Offhand
            default => [],
        };
    }

    public function getSelectedSlot(): int
    {
        return 0; // Simplified for now
    }
}
