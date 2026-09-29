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

use watermossmc\item\Item;
use watermossmc\network\mcpe\protocol\types\ItemStackDescriptor;

/**
 * Represents an item stack in the inventory.
 */
final class ItemStack
{
    /**
     * @param array<string, mixed> $nbt
     */
    public function __construct(
        public readonly Item $item,
        public int $count = 1,
        public int $damage = 0,
        public readonly array $nbt = []
    ) {}

    public function isEmpty(): bool
    {
        return $this->count <= 0;
    }

    public function getNumericId(): int
    {
        return $this->item->id;
    }

    public function getMaxStackSize(): int
    {
        return $this->item->getMaxStackSize();
    }

    /**
     * @return static a new stack with $count subtracted (never below zero).
     */
    public function withCountRemoved(int $count): self
    {
        return new self($this->item, max(0, $this->count - $count), $this->damage, $this->nbt);
    }

    public function toDescriptor(int $stackId): ItemStackDescriptor
    {
        return new ItemStackDescriptor($this->item->id, $this->count, $this->damage, $stackId, 0, '');
    }
}
