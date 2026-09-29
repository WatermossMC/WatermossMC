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

use watermossmc\crafting\CraftingManager;
use watermossmc\network\mcpe\protocol\serverbound\ItemStackRequest;

final class InventoryRequestExecutor
{
    /**
     * @param list<array{requestId: int, actions: list<array<string, mixed>>}> $requests
     * @return array{
     *     accepted: list<array{requestId: int, dirty: list<array{containerId: int, slot: int}>}>,
     *     rejected: list<int>
     * }
     */
    public static function execute(PlayerInventory $inventory, array $requests): array
    {
        $accepted = [];
        $rejected = [];

        foreach ($requests as $request) {
            $dirty = self::applyRequest($inventory, $request['actions']);
            if ($dirty === null) {
                $rejected[] = $request['requestId'];
                continue;
            }
            $accepted[] = ['requestId' => $request['requestId'], 'dirty' => $dirty];
        }

        return ['accepted' => $accepted, 'rejected' => $rejected];
    }

    /**
     * @param list<array<string, mixed>> $actions
     * @return list<array{containerId: int, slot: int}>|null null means the whole request is rejected
     */
    private static function applyRequest(PlayerInventory $inventory, array $actions): ?array
    {
        $dirty = [];

        foreach ($actions as $action) {
            $touched = match ($action['type']) {
                ItemStackRequest::ACTION_TAKE, ItemStackRequest::ACTION_PLACE => self::move($inventory, $action['source'], $action['destination'], $action['count']),
                ItemStackRequest::ACTION_SWAP => self::swap($inventory, $action['slot1'], $action['slot2']),
                ItemStackRequest::ACTION_DROP => self::drop($inventory, $action['source'], $action['count']),
                ItemStackRequest::ACTION_DESTROY => self::destroy($inventory, $action['source'], $action['count']),
                ItemStackRequest::ACTION_CRAFTING_RECIPE => self::craft($inventory, $action['recipeId'], $action['repetitions']),
                default => null, // remaining actions (creative, lab table, ...): nothing we can apply, but not fatal on their own
            };

            if ($touched === null) {
                continue;
            }
            if ($touched === false) {
                return null;
            }
            array_push($dirty, ...$touched);
        }

        return $dirty;
    }

    /**
     * @return list<array{containerId: int, slot: int}>|false
     */
    private static function move(PlayerInventory $inventory, $source, $destination, int $count): array|false
    {
        $sourceItem = $inventory->getItemAt($source->containerName->containerId, $source->slot);
        if ($sourceItem === null || $count <= 0 || $count > $sourceItem->count) {
            return false;
        }

        $destItem = $inventory->getItemAt($destination->containerName->containerId, $destination->slot);
        if ($destItem !== null) {
            if ($destItem->getNumericId() !== $sourceItem->getNumericId() || $destItem->damage !== $sourceItem->damage) {
                return false; // moving onto a different item is a swap, not a place
            }
            if ($destItem->count + $count > $destItem->getMaxStackSize()) {
                return false;
            }
            $destItem->count += $count;
        } else {
            $inventory->setItemAt($destination->containerName->containerId, $destination->slot, new ItemStack($sourceItem->item, $count, $sourceItem->damage, $sourceItem->nbt));
        }

        $remaining = $sourceItem->count - $count;
        $inventory->setItemAt($source->containerName->containerId, $source->slot, $remaining > 0 ? $sourceItem->withCountRemoved($count) : null);

        return [
            ['containerId' => $source->containerName->containerId, 'slot' => $source->slot],
            ['containerId' => $destination->containerName->containerId, 'slot' => $destination->slot],
        ];
    }

    /**
     * @return list<array{containerId: int, slot: int}>
     */
    private static function swap(PlayerInventory $inventory, $slot1, $slot2): array
    {
        $item1 = $inventory->getItemAt($slot1->containerName->containerId, $slot1->slot);
        $item2 = $inventory->getItemAt($slot2->containerName->containerId, $slot2->slot);

        $inventory->setItemAt($slot1->containerName->containerId, $slot1->slot, $item2);
        $inventory->setItemAt($slot2->containerName->containerId, $slot2->slot, $item1);

        return [
            ['containerId' => $slot1->containerName->containerId, 'slot' => $slot1->slot],
            ['containerId' => $slot2->containerName->containerId, 'slot' => $slot2->slot],
        ];
    }

    /**
     * @return list<array{containerId: int, slot: int}>|false
     */
    private static function drop(PlayerInventory $inventory, $source, int $count): array|false
    {
        // Dropping into the world isn't implemented yet (no item entity is spawned); the item is
        // simply removed from the inventory so the client's prediction stays in sync.
        return self::destroy($inventory, $source, $count);
    }

    /**
     * @return list<array{containerId: int, slot: int}>|false
     */
    private static function destroy(PlayerInventory $inventory, $source, int $count): array|false
    {
        $item = $inventory->getItemAt($source->containerName->containerId, $source->slot);
        if ($item === null || $count <= 0 || $count > $item->count) {
            return false;
        }

        $remaining = $item->count - $count;
        $inventory->setItemAt($source->containerName->containerId, $source->slot, $remaining > 0 ? $item->withCountRemoved($count) : null);

        return [['containerId' => $source->containerName->containerId, 'slot' => $source->slot]];
    }

    /**
     * Matches the current crafting grid (2x2 or 3x3, whichever the client has open) against the
     * requested recipe, consumes the ingredients, and places the result in the output preview
     * slot. The client sends a TAKE action straight after this to move the result into its
     * inventory, which is handled by the normal move() path since the output preview is just
     * another addressable slot.
     *
     * @return list<array{containerId: int, slot: int}>|false
     */
    private static function craft(PlayerInventory $inventory, int $recipeId, int $repetitions): array|false
    {
        $recipe = CraftingManager::getRecipe($recipeId);
        if ($recipe === null || $repetitions <= 0) {
            return false;
        }

        $grid3x3 = $inventory->getCrafting3x3Grid();
        $grid2x2 = $inventory->getCrafting2x2Grid();

        if ($recipe->matches($grid3x3, 3, 3)) {
            $width = 3;
            $height = 3;
            $grid = $grid3x3;
        } elseif ($recipe->matches($grid2x2, 2, 2)) {
            $width = 2;
            $height = 2;
            $grid = $grid2x2;
        } else {
            return false;
        }

        $dirty = [];
        for ($rep = 0; $rep < $repetitions; $rep++) {
            if (!$recipe->matches($grid, $width, $height)) {
                return false;
            }

            foreach ($grid as $i => $item) {
                if ($item === null || $item->isEmpty()) {
                    continue;
                }
                $grid[$i] = $item->withCountRemoved(1);
                if ($grid[$i]->isEmpty()) {
                    $grid[$i] = null;
                }
            }
        }

        $slotBase = $width === 3 ? PlayerInventory::UI_SLOT_CRAFTING_3X3_START : PlayerInventory::UI_SLOT_CRAFTING_2X2_START;
        foreach ($grid as $i => $item) {
            $inventory->setItemAt(PlayerInventory::UI_CONTAINER_ID, $slotBase + $i, $item);
            $dirty[] = ['containerId' => PlayerInventory::UI_CONTAINER_ID, 'slot' => $slotBase + $i];
        }

        $result = $recipe->getResult();
        if ($result === null) {
            // the recipe's result item isn't registered yet (e.g. a plugin hasn't added it) - reject
            return false;
        }
        $output = new ItemStack($result->item, $result->count * $repetitions, $result->damage, $result->nbt);
        $inventory->setCraftingOutput($output);
        $dirty[] = ['containerId' => PlayerInventory::UI_CONTAINER_ID, 'slot' => PlayerInventory::UI_SLOT_CRAFTING_OUTPUT];

        return $dirty;
    }
}
