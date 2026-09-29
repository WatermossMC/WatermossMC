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

namespace watermossmc\crafting;

use watermossmc\inventory\ItemStack;
use watermossmc\item\ItemRegistry;

/**
 * The declared output of a recipe, kept as an identifier rather than a resolved ItemStack so that
 * recipes for items a plugin hasn't registered yet still load (with a stable network id) and
 * start working the moment the item is registered.
 */
final class RecipeResult
{
    public function __construct(
        public readonly string $identifier,
        public readonly int $count,
        public readonly int $damage = 0
    ) {}

    public function resolve(): ?ItemStack
    {
        $item = ItemRegistry::getByIdentifier($this->identifier);
        if ($item === null) {
            return null;
        }
        return new ItemStack($item, $this->count, $this->damage);
    }
}
