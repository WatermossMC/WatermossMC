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

final class CraftingManager
{
    /** @var list<CraftingRecipe> */
    private static array $recipes = [];

    private static bool $loaded = false;

    public static function init(string $jsonPath): void
    {
        self::$recipes = [];
        self::$loaded = true;

        $raw = json_decode(file_get_contents($jsonPath), true);
        if (!\is_array($raw)) {
            return;
        }

        $tags = $raw['tags'] ?? [];
        foreach ($raw['recipes'] ?? [] as $entry) {
            $recipe = self::parseRecipe($entry, $tags);
            if ($recipe !== null) {
                self::$recipes[] = $recipe;
            }
        }
    }

    /**
     * @param array<string, mixed>        $entry
     * @param array<string, list<string>> $tags
     */
    private static function parseRecipe(array $entry, array $tags): ?CraftingRecipe
    {
        $result = self::parseResult($entry['result'] ?? null);
        if ($result === null) {
            return null;
        }

        $priority = (int) ($entry['priority'] ?? 0);
        $id = (string) ($entry['id'] ?? '');

        if (($entry['type'] ?? '') === 'shaped') {
            $shape = $entry['shape'] ?? [];
            $ingredients = [];
            foreach ($entry['ingredients'] ?? [] as $symbol => $data) {
                $ingredient = self::parseIngredient($data, $tags);
                if ($ingredient === null) {
                    return null;
                }
                $ingredients[$symbol] = $ingredient;
            }
            return new ShapedRecipe($id, $shape, $ingredients, $result, $priority);
        }

        if (($entry['type'] ?? '') === 'shapeless') {
            $ingredients = [];
            foreach ($entry['ingredients'] ?? [] as $data) {
                $ingredient = self::parseIngredient($data, $tags);
                if ($ingredient === null) {
                    return null;
                }
                $ingredients[] = $ingredient;
            }
            return new ShapelessRecipe($id, $ingredients, $result, $priority);
        }

        return null;
    }

    /**
     * @param array<string, mixed>        $data
     * @param array<string, list<string>> $tags
     */
    private static function parseIngredient(array $data, array $tags): ?RecipeIngredient
    {
        $count = (int) ($data['count'] ?? 1);

        if (isset($data['tag'])) {
            $members = $tags[$data['tag']] ?? [];
            return new RecipeIngredient(null, $data['tag'], $members, $count);
        }

        if (isset($data['identifier'])) {
            return new RecipeIngredient($data['identifier'], null, [], $count);
        }

        return null;
    }

    /**
     * The result is kept as an identifier (not resolved against the item registry) so that a
     * recipe for an item a plugin hasn't registered yet still loads, keeps a stable network id,
     * and starts working automatically once that item is registered.
     *
     * @param array<string, mixed>|null $data
     */
    private static function parseResult(?array $data): ?RecipeResult
    {
        if ($data === null || !isset($data['identifier'])) {
            return null;
        }
        return new RecipeResult(
            (string) $data['identifier'],
            (int) ($data['count'] ?? 1),
            (int) ($data['damage'] ?? 0)
        );
    }

    /**
     * @return list<CraftingRecipe>
     */
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
