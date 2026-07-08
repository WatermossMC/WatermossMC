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
}
