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

/**
 * A recipe that only cares which ingredients are present in the grid, regardless of arrangement.
 */
final class ShapelessRecipe implements CraftingRecipe
{
    /**
     * @param list<RecipeIngredient> $ingredients
     */
    public function __construct(
        private readonly string $recipeId,
        private readonly array $ingredients,
        private readonly RecipeResult $result,
        private readonly int $priority = 0
    ) {}

    public function getRecipeId(): string
    {
        return $this->recipeId;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return list<RecipeIngredient>
     */
    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    public function matches(array $grid, int $width, int $height): bool
    {
        $remaining = $this->ingredients;

        foreach ($grid as $item) {
            if ($item === null || $item->isEmpty()) {
                continue;
            }

            $matchedIndex = null;
            foreach ($remaining as $index => $ingredient) {
                if ($ingredient->accepts($item->item->identifier) && $item->count >= $ingredient->count) {
                    $matchedIndex = $index;
                    break;
                }
            }

            if ($matchedIndex === null) {
                return false;
            }
            unset($remaining[$matchedIndex]);
        }

        return \count($remaining) === 0;
    }

    public function getResult(): ?ItemStack
    {
        return $this->result->resolve();
    }

    public function getRawResult(): RecipeResult
    {
        return $this->result;
    }
}
