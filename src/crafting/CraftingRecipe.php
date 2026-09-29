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

interface CraftingRecipe
{
    public function getRecipeId(): string;

    public function getPriority(): int;

    /**
     * @param array<int, ItemStack|null> $grid indexed 0..(width*height-1), row-major
     */
    public function matches(array $grid, int $width, int $height): bool;

    /**
     * Resolves the result item lazily against the current item registry. Returns null if the
     * result item isn't registered (e.g. a plugin hasn't added it yet), so the recipe exists and
     * keeps its stable network id, but can't actually be crafted until the item is available.
     */
    public function getResult(): ?ItemStack;
}
