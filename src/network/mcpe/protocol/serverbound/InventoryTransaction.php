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

use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\types\BlockPosition;
use watermossmc\network\mcpe\protocol\types\ItemStackDescriptor;

/**
 * Parses InventoryTransactionPacket. Only the parts needed to detect "player right-clicked a
 * block with an empty/non-placeable hand" (to open a block's UI, e.g. a crafting table) are
 * decoded in detail; the generic NORMAL transaction's slot actions are skipped correctly (so the
 * packet parses without error) but not interpreted, since normal inventory moves already go
 * through ItemStackRequestPacket in this implementation.
 */
final class InventoryTransaction
{
    public const TYPE_NORMAL = 0;
    public const TYPE_MISMATCH = 1;
    public const TYPE_USE_ITEM = 2;
    public const TYPE_USE_ITEM_ON_ENTITY = 3;
    public const TYPE_RELEASE_ITEM = 4;
    public const ACTION_CLICK_BLOCK = 0;
    public const ACTION_CLICK_AIR = 1;
    public const ACTION_BREAK_BLOCK = 2;
    public const ACTION_USE_AS_ATTACK = 3;

    /**
     * @return array{transactionType: int, actionType?: int, blockPosition?: BlockPosition, face?: int, heldItem?: ItemStackDescriptor}
     */
    public static function read(string $p, int &$o): array
    {
        McpeBinary::readSignedVarInt($p, $o); // legacy request id
        $hasChangedSlots = McpeBinary::readBool($p, $o);
        if ($hasChangedSlots) {
            $len = McpeBinary::readUnsignedVarInt($p, $o);
            for ($i = 0; $i < $len; $i++) {
                McpeBinary::readUnsignedByte($p, $o); // container id
                $slotCount = McpeBinary::readUnsignedVarInt($p, $o);
                for ($j = 0; $j < $slotCount; $j++) {
                    McpeBinary::readUnsignedByte($p, $o);
                }
            }
        }

        $transactionType = McpeBinary::readUnsignedVarInt($p, $o);

        $actionCount = McpeBinary::readUnsignedVarInt($p, $o);
        for ($i = 0; $i < $actionCount; $i++) {
            self::skipAction($p, $o);
        }

        if ($transactionType === self::TYPE_USE_ITEM) {
            $actionType = McpeBinary::readSignedVarInt($p, $o);
            McpeBinary::readUnsignedByte($p, $o); // trigger type
            $blockPosition = BlockPosition::read($p, $o);
            $face = McpeBinary::readUnsignedByte($p, $o);
            McpeBinary::readSignedVarInt($p, $o); // hotbar slot
            McpeBinary::readUnsignedByte($p, $o); // hand slot
            $heldItem = ItemStackDescriptor::read($p, $o);
            McpeBinary::readFloat($p, $o); // player position x
            McpeBinary::readFloat($p, $o); // player position y
            McpeBinary::readFloat($p, $o); // player position z
            McpeBinary::readFloat($p, $o); // click position x
            McpeBinary::readFloat($p, $o); // click position y
            McpeBinary::readFloat($p, $o); // click position z
            McpeBinary::readUnsignedVarInt($p, $o); // block runtime id
            McpeBinary::readUnsignedByte($p, $o); // client interact prediction
            McpeBinary::readByte($p, $o); // client cooldown state

            return [
                'transactionType' => $transactionType,
                'actionType' => $actionType,
                'blockPosition' => $blockPosition,
                'face' => $face,
                'heldItem' => $heldItem,
            ];
        }

        // other transaction types (normal, use-on-entity, release, mismatch) aren't consumed by
        // this server; nothing more to read since we already consumed the shared action list.
        return ['transactionType' => $transactionType];
    }

    private static function skipAction(string $p, int &$o): void
    {
        McpeBinary::readUnsignedVarInt($p, $o); // source type
        McpeBinary::readOptional($p, $o, static fn (string $p, int &$o) => McpeBinary::readSignedByte($p, $o)); // window id
        McpeBinary::readOptional($p, $o, static fn (string $p, int &$o) => McpeBinary::readUnsignedVarInt($p, $o)); // source flags
        McpeBinary::readUnsignedVarInt($p, $o); // inventory slot
        ItemStackDescriptor::read($p, $o); // old item
        ItemStackDescriptor::read($p, $o); // new item
    }
}
