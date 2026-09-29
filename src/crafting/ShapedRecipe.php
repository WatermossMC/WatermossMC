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
 * A recipe that requires ingredients to be arranged in a specific shape within the crafting grid.
 * The shape may be matched anywhere within the grid (not necessarily starting at slot 0).
 */
final class ShapedRecipe implements CraftingRecipe
{
    /**
     * @param list<string>                    $shape       rows of symbols, e.g. ["AB", " A"]; space means empty
     * @param array<string, RecipeIngredient> $ingredients symbol => ingredient
     */
    public function __construct(
        private readonly string $recipeId,
        private readonly array $shape,
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

    public function getShapeWidth(): int
    {
        return \strlen($this->shape[0] ?? '');
    }

    public function getShapeHeight(): int
    {
        return \count($this->shape);
    }

    /**
     * @return array<string, RecipeIngredient>
     */
    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    /**
     * @return list<string>
     */
    public function getShape(): array
    {
        return $this->shape;
    }

    public function matches(array $grid, int $width, int $height): bool
    {
        $shapeW = $this->getShapeWidth();
        $shapeH = $this->getShapeHeight();

        for ($offsetY = 0; $offsetY <= $height - $shapeH; $offsetY++) {
            for ($offsetX = 0; $offsetX <= $width - $shapeW; $offsetX++) {
                if ($this->matchesAt($grid, $width, $height, $offsetX, $offsetY, $shapeW, $shapeH)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function matchesAt(array $grid, int $width, int $height, int $offsetX, int $offsetY, int $shapeW, int $shapeH): bool
    {
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $item = $grid[$y * $width + $x] ?? null;

                $inShape = $x >= $offsetX && $x < $offsetX + $shapeW && $y >= $offsetY && $y < $offsetY + $shapeH;
                if (!$inShape) {
                    if ($item !== null && !$item->isEmpty()) {
                        return false;
                    }
                    continue;
                }

                $symbol = $this->shape[$y - $offsetY][$x - $offsetX];
                if ($symbol === ' ') {
                    if ($item !== null && !$item->isEmpty()) {
                        return false;
                    }
                    continue;
                }

                $ingredient = $this->ingredients[$symbol] ?? null;
                if ($ingredient === null || $item === null || $item->isEmpty()) {
                    return false;
                }
                if (!$ingredient->accepts($item->item->identifier) || $item->count < $ingredient->count) {
                    return false;
                }
            }
        }

        return true;
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
