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

namespace watermossmc\network\mcpe\protocol\serverbound;

use RuntimeException;
use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStackRequestSlotInfo;

final class ItemStackRequest
{
    public const ACTION_TAKE = 0;
    public const ACTION_PLACE = 1;
    public const ACTION_SWAP = 2;
    public const ACTION_DROP = 3;
    public const ACTION_DESTROY = 4;
    public const ACTION_CRAFTING_CONSUME_INPUT = 5;
    public const ACTION_CRAFTING_CREATE_SPECIFIC_RESULT = 6;
    public const ACTION_LAB_TABLE_COMBINE = 7;
    public const ACTION_BEACON_PAYMENT = 8;
    public const ACTION_MINE_BLOCK = 9;
    public const ACTION_CRAFTING_RECIPE = 10;
    public const ACTION_CRAFTING_RECIPE_AUTO = 11;
    public const ACTION_CREATIVE_CREATE = 12;
    public const ACTION_CRAFTING_RECIPE_OPTIONAL = 13;
    public const ACTION_CRAFTING_GRINDSTONE = 14;
    public const ACTION_CRAFTING_LOOM = 15;
    public const ACTION_CRAFTING_NON_IMPLEMENTED = 16;
    public const ACTION_CRAFTING_RESULTS_DEPRECATED = 17;

    /**
     * @return array{requestId: int, actions: list<array{type: int, count?: int, source?: ItemStackRequestSlotInfo, destination?: ItemStackRequestSlotInfo, slot1?: ItemStackRequestSlotInfo, slot2?: ItemStackRequestSlotInfo, randomly?: bool, recipeId?: int, repetitions?: int}>}
     */
    public static function read(string $p, int &$o): array
    {
        $requestId = McpeBinary::readSignedVarInt($p, $o);
        $actions = [];

        $actionCount = McpeBinary::readUnsignedVarInt($p, $o);
        for ($i = 0; $i < $actionCount; $i++) {
            $type = McpeBinary::readUnsignedVarInt($p, $o);
            McpeBinary::readUnsignedByte($p, $o); // legacy type id, unused
            $actions[] = self::readAction($p, $o, $type);
        }

        $filterStringCount = McpeBinary::readUnsignedVarInt($p, $o);
        for ($i = 0; $i < $filterStringCount; $i++) {
            McpeBinary::readString($p, $o);
        }
        McpeBinary::readLInt($p, $o); // filter string cause

        return ['requestId' => $requestId, 'actions' => $actions];
    }

    /**
     * @return array{type: int, count?: int, source?: ItemStackRequestSlotInfo, destination?: ItemStackRequestSlotInfo, slot1?: ItemStackRequestSlotInfo, slot2?: ItemStackRequestSlotInfo, randomly?: bool}
     */
    private static function readAction(string $p, int &$o, int $type): array
    {
        switch ($type) {
            case self::ACTION_TAKE:
            case self::ACTION_PLACE:
                $count = McpeBinary::readUnsignedByte($p, $o);
                $source = ItemStackRequestSlotInfo::read($p, $o);
                $destination = ItemStackRequestSlotInfo::read($p, $o);
                return ['type' => $type, 'count' => $count, 'source' => $source, 'destination' => $destination];
            case self::ACTION_SWAP:
                $slot1 = ItemStackRequestSlotInfo::read($p, $o);
                $slot2 = ItemStackRequestSlotInfo::read($p, $o);
                return ['type' => $type, 'slot1' => $slot1, 'slot2' => $slot2];
            case self::ACTION_DROP:
                $count = McpeBinary::readUnsignedByte($p, $o);
                $source = ItemStackRequestSlotInfo::read($p, $o);
                $randomly = McpeBinary::readBool($p, $o);
                return ['type' => $type, 'count' => $count, 'source' => $source, 'randomly' => $randomly];
            case self::ACTION_DESTROY:
            case self::ACTION_CRAFTING_CONSUME_INPUT:
                $count = McpeBinary::readUnsignedByte($p, $o);
                $source = ItemStackRequestSlotInfo::read($p, $o);
                return ['type' => $type, 'count' => $count, 'source' => $source];
            case self::ACTION_CRAFTING_CREATE_SPECIFIC_RESULT:
                McpeBinary::readUnsignedByte($p, $o); // result slot
                return ['type' => $type];
            case self::ACTION_LAB_TABLE_COMBINE:
            case self::ACTION_CRAFTING_NON_IMPLEMENTED:
                return ['type' => $type];
            case self::ACTION_BEACON_PAYMENT:
                McpeBinary::readSignedVarInt($p, $o);
                McpeBinary::readSignedVarInt($p, $o);
                return ['type' => $type];
            case self::ACTION_MINE_BLOCK:
                McpeBinary::readSignedVarInt($p, $o);
                McpeBinary::readSignedVarInt($p, $o);
                McpeBinary::readLInt($p, $o);
                return ['type' => $type];
            case self::ACTION_CRAFTING_RECIPE:
                $recipeId = McpeBinary::readSignedVarInt($p, $o);
                $repetitions = McpeBinary::readUnsignedByte($p, $o);
                return ['type' => $type, 'recipeId' => $recipeId, 'repetitions' => $repetitions];
            case self::ACTION_CRAFTING_GRINDSTONE:
                McpeBinary::readSignedVarInt($p, $o); // recipe id
                McpeBinary::readSignedVarInt($p, $o); // repair cost
                McpeBinary::readUnsignedByte($p, $o); // repetitions
                return ['type' => $type];
            case self::ACTION_CRAFTING_RECIPE_AUTO:
                McpeBinary::readSignedVarInt($p, $o); // recipe id
                McpeBinary::readUnsignedByte($p, $o); // repetitions
                $count = McpeBinary::readUnsignedVarInt($p, $o);
                for ($i = 0; $i < $count; $i++) {
                    // Ingredient payloads vary by descriptor type; since crafting isn't implemented,
                    // we can't safely skip an arbitrary one, so bail out of this request.
                    throw new RuntimeException('Crafting recipe auto actions are not supported');
                }
                return ['type' => $type];
            case self::ACTION_CREATIVE_CREATE:
                McpeBinary::readUnsignedVarInt($p, $o); // creative item net id
                McpeBinary::readUnsignedByte($p, $o); // repetitions
                return ['type' => $type];
            case self::ACTION_CRAFTING_RECIPE_OPTIONAL:
                McpeBinary::readSignedVarInt($p, $o); // recipe id
                McpeBinary::readLInt($p, $o); // filter string index
                return ['type' => $type];
            case self::ACTION_CRAFTING_LOOM:
                McpeBinary::readString($p, $o); // pattern id
                McpeBinary::readUnsignedByte($p, $o); // repetitions
                return ['type' => $type];
            case self::ACTION_CRAFTING_RESULTS_DEPRECATED:
                $count = McpeBinary::readUnsignedVarInt($p, $o);
                if ($count > 0) {
                    throw new RuntimeException('Deprecated crafting-results actions are not supported');
                }
                McpeBinary::readUnsignedByte($p, $o);
                return ['type' => $type];
            default:
                throw new RuntimeException("Unhandled item stack request action type {$type}");
        }
    }
}
