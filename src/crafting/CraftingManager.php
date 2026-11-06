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

use watermossmc\util\Logger;

/** Loads crafting recipes from the vanilla-format files under resources/data/recipes/. */
final class CraftingManager
{
    /** @var CraftingRecipe[] */
    private static array $recipes = [];

    private static bool $loaded = false;

    public static function init(string $dataDir): void
    {
        self::$recipes = [];
        self::$loaded = true;

        $index = 0;
        foreach (['shaped_crafting.json', 'shaped_crafting_asymmetric.json'] as $file) {
            $index = self::loadFile($dataDir . '/' . $file, true, $index);
        }
        self::loadFile($dataDir . '/shapeless_crafting.json', false, $index);
    }

    private static function loadFile(string $path, bool $shaped, int $index): int
    {
        if (!is_file($path)) {
            Logger::warning("[Crafting] Missing recipe file: {$path}");
            return $index;
        }
        $raw = json_decode((string) file_get_contents($path), true);
        if (!\is_array($raw)) {
            Logger::warning("[Crafting] Invalid JSON in {$path}");
            return $index;
        }
        foreach ($raw as $entry) {
            if (!\is_array($entry)) {
                continue;
            }
            $recipe = $shaped ? self::parseShaped($entry, $index) : self::parseShapeless($entry, $index);
            if ($recipe !== null) {
                self::$recipes[] = $recipe;
                $index++;
            }
        }
        return $index;
    }

    private static function parseShaped(array $entry, int $index): ?ShapedRecipe
    {
        $result = self::firstResult($entry['output'] ?? null);
        $shape = $entry['shape'] ?? null;
        if ($result === null || !\is_array($shape) || $shape === []) {
            return null;
        }
        $ingredients = [];
        foreach ($entry['input'] ?? [] as $symbol => $data) {
            $ingredient = self::parseIngredient($data);
            if ($ingredient === null) {
                return null;
            }
            $ingredients[$symbol] = $ingredient;
        }
        $id = (string) ($entry['block'] ?? 'recipe') . '_' . $index;
        return new ShapedRecipe($id, array_values($shape), $ingredients, $result, (int) ($entry['priority'] ?? 0));
    }

    private static function parseShapeless(array $entry, int $index): ?ShapelessRecipe
    {
        $result = self::firstResult($entry['output'] ?? null);
        if ($result === null) {
            return null;
        }
        $ingredients = [];
        foreach ($entry['input'] ?? [] as $data) {
            $ingredient = self::parseIngredient($data);
            if ($ingredient === null) {
                return null;
            }
            $ingredients[] = $ingredient;
        }
        if ($ingredients === []) {
            return null;
        }
        $id = (string) ($entry['block'] ?? 'recipe') . '_' . $index;
        return new ShapelessRecipe($id, $ingredients, $result, (int) ($entry['priority'] ?? 0));
    }

    /** One input entry: a bare identifier string, or {name, count?, meta?, block_states?}. */
    private static function parseIngredient(mixed $data): ?RecipeIngredient
    {
        if (\is_string($data)) {
            return new RecipeIngredient($data, null, [], 1);
        }
        if (\is_array($data) && isset($data['name']) && \is_string($data['name'])) {
            // block_states-based variants are not distinguished here; matched by identifier only.
            return new RecipeIngredient($data['name'], null, [], (int) ($data['count'] ?? 1));
        }
        return null;
    }

    /** "output" is a list; the first non-block_states entry is used as the recipe result. */
    private static function firstResult(mixed $output): ?RecipeResult
    {
        if (!\is_array($output)) {
            return null;
        }
        foreach ($output as $entry) {
            if (\is_string($entry)) {
                return new RecipeResult($entry, 1, 0);
            }
            if (\is_array($entry) && isset($entry['name']) && \is_string($entry['name'])) {
                return new RecipeResult($entry['name'], (int) ($entry['count'] ?? 1), (int) ($entry['meta'] ?? 0));
            }
        }
        return null;
    }

    /** @return CraftingRecipe[] */
    public static function getRecipes(): array
    {
        return self::$recipes;
    }

    public static function getRecipe(int $index): ?CraftingRecipe
    {
        return self::$recipes[$index] ?? null;
    }

    public static function isLoaded(): bool
    {
        return self::$loaded;
    }
}
