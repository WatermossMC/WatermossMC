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
use watermossmc\network\mcpe\protocol\types\ContainerIds;

final class PlayerInventory
{
    public const MAX_SIZE = 41;
    public const SLOT_INVENTORY_OFFSET = 9;
    public const SLOT_HOTBAR_OFFSET = 36;
    private const SLOT_ARMOR_OFFSET = 36;
    private const SLOT_OFFHAND = 40;
    public const UI_CONTAINER_ID = ContainerIds::UI;
    public const UI_SLOT_CURSOR = 59;
    public const UI_SLOT_CRAFTING_2X2_START = 28;
    public const UI_SLOT_CRAFTING_3X3_START = 32;
    public const UI_SLOT_CRAFTING_OUTPUT = 50;
    private const CRAFTING_GRID_SIZE = 9;

    /** @var array<int, ItemStack|null> */
    private array $slots;

    /** @var array<int, int> parallel to $slots; 0 means the slot has no tracked network stack id */
    private array $stackIds;

    /** @var array<int, ItemStack|null> the 3x3 crafting grid backing array; the 2x2 grid uses indices 0-3 of it */
    private array $craftingGrid;

    /** @var array<int, int> parallel to $craftingGrid */
    private array $craftingGridStackIds;

    private ?ItemStack $craftingOutput = null;

    private int $craftingOutputStackId = 0;

    private ?ItemStack $cursor = null;

    private int $cursorStackId = 0;

    private int $nextStackId = 1;

    private int $selectedSlot = 0;

    public function __construct()
    {
        $this->slots = array_fill(0, self::MAX_SIZE, null);
        $this->stackIds = array_fill(0, self::MAX_SIZE, 0);
        $this->craftingGrid = array_fill(0, self::CRAFTING_GRID_SIZE, null);
        $this->craftingGridStackIds = array_fill(0, self::CRAFTING_GRID_SIZE, 0);
    }

    public function setItem(int $slot, ?ItemStack $item): void
    {
        if ($slot < 0 || $slot >= self::MAX_SIZE) {
            throw new OutOfBoundsException("Inventory slot out of bounds: {$slot}");
        }
        $this->slots[$slot] = $item;
        $this->stackIds[$slot] = 0;
    }

    public function getItem(int $slot): ?ItemStack
    {
        if ($slot < 0 || $slot >= self::MAX_SIZE) {
            return null;
        }
        return $this->slots[$slot] ?? null;
    }

    public function addItem(ItemStack $item): int
    {
        $remaining = $item->count;
        $max = $item->getMaxStackSize();

        for ($i = 0; $i < 36 && $remaining > 0; $i++) {
            $existing = $this->slots[$i];
            if ($existing === null || $existing->getNumericId() !== $item->getNumericId() || $existing->damage !== $item->damage) {
                continue;
            }
            $moved = min($remaining, $max - $existing->count);
            if ($moved <= 0) {
                continue;
            }
            $existing->count += $moved;
            $this->stackIds[$i] = 0;
            $remaining -= $moved;
        }

        for ($i = 0; $i < 36 && $remaining > 0; $i++) {
            if ($this->slots[$i] !== null) {
                continue;
            }
            $moved = min($remaining, $max);
            $this->slots[$i] = new ItemStack($item->item, $moved, $item->damage, $item->nbt);
            $this->stackIds[$i] = 0;
            $remaining -= $moved;
        }

        return $remaining;
    }

    /**
     * @return array<int, ItemStack|null>
     */
    public function getWindowItems(int $windowId): array
    {
        return match ($windowId) {
            ContainerIds::INVENTORY => \array_slice($this->slots, 0, 36),
            ContainerIds::ARMOR => \array_slice($this->slots, self::SLOT_ARMOR_OFFSET, 4),
            ContainerIds::OFFHAND => [$this->slots[self::SLOT_OFFHAND] ?? null],
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

    public function getSelectedItem(): ?ItemStack
    {
        return $this->getItem($this->selectedSlot);
    }

    public function getHolder(): mixed
    {
        return null;
    }

    public function resolveAbsoluteSlot(int $containerId, int $slot): ?int
    {
        return match ($containerId) {
            ContainerIds::INVENTORY => $slot >= 0 && $slot < 36 ? $slot : null,
            ContainerIds::ARMOR => $slot >= 0 && $slot < 4 ? self::SLOT_ARMOR_OFFSET + $slot : null,
            ContainerIds::OFFHAND => $slot === 0 ? self::SLOT_OFFHAND : null,
            default => null,
        };
    }

    public function isCursorSlot(int $containerId, int $slot): bool
    {
        return $containerId === self::UI_CONTAINER_ID && $slot === self::UI_SLOT_CURSOR;
    }

    private function resolveCraftingGridIndex(int $containerId, int $slot): ?int
    {
        if ($containerId !== self::UI_CONTAINER_ID) {
            return null;
        }
        if ($slot >= self::UI_SLOT_CRAFTING_2X2_START && $slot < self::UI_SLOT_CRAFTING_2X2_START + 4) {
            return $slot - self::UI_SLOT_CRAFTING_2X2_START;
        }
        if ($slot >= self::UI_SLOT_CRAFTING_3X3_START && $slot < self::UI_SLOT_CRAFTING_3X3_START + self::CRAFTING_GRID_SIZE) {
            return $slot - self::UI_SLOT_CRAFTING_3X3_START;
        }
        return null;
    }

    private function isCraftingOutputSlot(int $containerId, int $slot): bool
    {
        return $containerId === self::UI_CONTAINER_ID && $slot === self::UI_SLOT_CRAFTING_OUTPUT;
    }

    /**
     * @return array<int, ItemStack|null> the 4 slots of the 2x2 crafting grid, row-major
     */
    public function getCrafting2x2Grid(): array
    {
        return [$this->craftingGrid[0], $this->craftingGrid[1], $this->craftingGrid[2], $this->craftingGrid[3]];
    }

    /**
     * @return array<int, ItemStack|null> the 9 slots of the 3x3 crafting grid, row-major
     */
    public function getCrafting3x3Grid(): array
    {
        return $this->craftingGrid;
    }

    public function clearCraftingGrid(): void
    {
        $this->craftingGrid = array_fill(0, self::CRAFTING_GRID_SIZE, null);
        $this->craftingGridStackIds = array_fill(0, self::CRAFTING_GRID_SIZE, 0);
    }

    public function setCraftingOutput(?ItemStack $item): void
    {
        $this->craftingOutput = $item;
        $this->craftingOutputStackId = 0;
    }

    public function getCraftingOutput(): ?ItemStack
    {
        return $this->craftingOutput;
    }

    public function getItemAt(int $containerId, int $slot): ?ItemStack
    {
        if ($this->isCursorSlot($containerId, $slot)) {
            return $this->cursor;
        }
        if ($this->isCraftingOutputSlot($containerId, $slot)) {
            return $this->craftingOutput;
        }
        $craftingIndex = $this->resolveCraftingGridIndex($containerId, $slot);
        if ($craftingIndex !== null) {
            return $this->craftingGrid[$craftingIndex];
        }
        $index = $this->resolveAbsoluteSlot($containerId, $slot);
        return $index === null ? null : $this->slots[$index];
    }

    public function setItemAt(int $containerId, int $slot, ?ItemStack $item): bool
    {
        if ($this->isCursorSlot($containerId, $slot)) {
            $this->cursor = $item;
            $this->cursorStackId = 0;
            return true;
        }
        if ($this->isCraftingOutputSlot($containerId, $slot)) {
            $this->craftingOutput = $item;
            $this->craftingOutputStackId = 0;
            return true;
        }
        $craftingIndex = $this->resolveCraftingGridIndex($containerId, $slot);
        if ($craftingIndex !== null) {
            $this->craftingGrid[$craftingIndex] = $item;
            $this->craftingGridStackIds[$craftingIndex] = 0;
            return true;
        }
        $index = $this->resolveAbsoluteSlot($containerId, $slot);
        if ($index === null) {
            return false;
        }
        $this->slots[$index] = $item;
        $this->stackIds[$index] = 0;
        return true;
    }

    public function getOrAllocateStackId(int $containerId, int $slot): int
    {
        if ($this->isCursorSlot($containerId, $slot)) {
            if ($this->cursor === null) {
                return 0;
            }
            if ($this->cursorStackId === 0) {
                $this->cursorStackId = $this->nextStackId++;
            }
            return $this->cursorStackId;
        }

        if ($this->isCraftingOutputSlot($containerId, $slot)) {
            if ($this->craftingOutput === null) {
                return 0;
            }
            if ($this->craftingOutputStackId === 0) {
                $this->craftingOutputStackId = $this->nextStackId++;
            }
            return $this->craftingOutputStackId;
        }

        $craftingIndex = $this->resolveCraftingGridIndex($containerId, $slot);
        if ($craftingIndex !== null) {
            if ($this->craftingGrid[$craftingIndex] === null) {
                return 0;
            }
            if ($this->craftingGridStackIds[$craftingIndex] === 0) {
                $this->craftingGridStackIds[$craftingIndex] = $this->nextStackId++;
            }
            return $this->craftingGridStackIds[$craftingIndex];
        }

        $index = $this->resolveAbsoluteSlot($containerId, $slot);
        if ($index === null || $this->slots[$index] === null) {
            return 0;
        }
        if ($this->stackIds[$index] === 0) {
            $this->stackIds[$index] = $this->nextStackId++;
        }
        return $this->stackIds[$index];
    }
}
