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

namespace watermossmc\network\mcpe\protocol\clientbound;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\crafting\CraftingManager;
use watermossmc\crafting\RecipeIngredient;
use watermossmc\crafting\ShapedRecipe;
use watermossmc\crafting\ShapelessRecipe;
use watermossmc\inventory\ItemStack;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\types\ItemStackDescriptor;
use watermossmc\network\Session;

final class CraftingData extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $payload = '';

        $shapeless = [];
        $shaped = [];
        foreach (CraftingManager::getRecipes() as $index => $recipe) {
            if ($recipe instanceof ShapedRecipe) {
                $shaped[] = [$index, $recipe];
            } elseif ($recipe instanceof ShapelessRecipe) {
                $shapeless[] = [$index, $recipe];
            }
        }

        // shapeless recipes
        $payload .= McpeBinary::writeUnsignedVarInt(\count($shapeless));
        foreach ($shapeless as [$netId, $recipe]) {
            $payload .= self::encodeShapelessRecipe($recipe, $netId);
        }

        // shaped recipes
        $payload .= McpeBinary::writeUnsignedVarInt(\count($shaped));
        foreach ($shaped as [$netId, $recipe]) {
            $payload .= self::encodeShapedRecipe($recipe, $netId);
        }

        // furnace recipe count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // furnace aux recipe count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // pps / multi recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // material reducer recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // smithing transform recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // smithing trim recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // recipes with type IDs count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // potion type recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // potion container recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // material reducer recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // cleanRecipes (bool)
        $payload .= Binary::writeBool(true);

        self::sendBatch(ProtocolInfo::CRAFTING_DATA_PACKET, $payload, $s, $sock);
    }

    private static function encodeShapelessRecipe(ShapelessRecipe $recipe, int $netId): string
    {
        $out = McpeBinary::writeString($recipe->getRecipeId());

        $ingredients = $recipe->getIngredients();
        $out .= McpeBinary::writeUnsignedVarInt(\count($ingredients));
        foreach ($ingredients as $ingredient) {
            $out .= self::encodeIngredient($ingredient);
        }

        $out .= self::encodeOutputs([$recipe->getResult()]);
        $out .= McpeBinary::writeUUID(self::recipeUuid($netId));
        $out .= McpeBinary::writeString('crafting_table');
        $out .= McpeBinary::writeSignedVarInt(0); // priority
        $out .= Binary::writeBool(false); // has unlocking requirement
        $out .= McpeBinary::writeSignedVarInt($netId);

        return $out;
    }

    private static function encodeShapedRecipe(ShapedRecipe $recipe, int $netId): string
    {
        $out = McpeBinary::writeString($recipe->getRecipeId());
        $out .= McpeBinary::writeSignedVarInt($recipe->getShapeWidth());
        $out .= McpeBinary::writeSignedVarInt($recipe->getShapeHeight());

        $ingredients = $recipe->getIngredients();
        foreach (str_split(implode('', $recipe->getShape())) as $symbol) {
            $ingredient = $symbol === ' ' ? null : ($ingredients[$symbol] ?? null);
            $out .= self::encodeIngredient($ingredient);
        }

        $out .= self::encodeOutputs([$recipe->getResult()]);
        $out .= McpeBinary::writeUUID(self::recipeUuid($netId));
        $out .= McpeBinary::writeString('crafting_table');
        $out .= McpeBinary::writeSignedVarInt(0); // priority
        $out .= Binary::writeBool(false); // has unlocking requirement
        $out .= McpeBinary::writeSignedVarInt($netId);

        return $out;
    }

    private static function encodeIngredient(?RecipeIngredient $ingredient): string
    {
        if ($ingredient === null) {
            $out = McpeBinary::writeUnsignedVarInt(0); // no descriptor
            $out .= McpeBinary::writeSignedVarInt(0x7fff); // empty aux value
            $out .= McpeBinary::writeSignedVarInt(0);
            return $out;
        }

        $identifier = $ingredient->identifier ?? ($ingredient->tagMembers[0] ?? '');

        $out = McpeBinary::writeUnsignedVarInt(1); // "name" descriptor present
        $out .= McpeBinary::writeString('name');
        $out .= McpeBinary::writeString($identifier);
        $out .= McpeBinary::writeSignedVarInt(0); // aux value (damage)
        $out .= McpeBinary::writeSignedVarInt($ingredient->count);

        return $out;
    }

    /**
     * @param list<ItemStack|null> $outputs
     */
    private static function encodeOutputs(array $outputs): string
    {
        $out = McpeBinary::writeUnsignedVarInt(\count($outputs));
        foreach ($outputs as $item) {
            $out .= ($item?->toDescriptor(0) ?? ItemStackDescriptor::air())->write();
        }
        return $out;
    }

    private static function recipeUuid(int $netId): string
    {
        $hex = str_pad(dechex($netId), 12, '0', STR_PAD_LEFT);
        return "00000000-0000-0000-0000-{$hex}";
    }
}
