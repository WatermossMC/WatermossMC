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

namespace watermossmc\mcpe\protocol\clientbound;

use function count;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\inventory\ItemStack;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\ProtocolInfo;

final class InventoryContent extends Packet
{
    // Window IDs
    public const WINDOW_INVENTORY = 0;
    public const WINDOW_ARMOR = 6;
    public const WINDOW_OFFHAND = 119;

    public static function sendEmpty(Session $s, Socket $sock, int $windowId): void
    {
        // Create a null-filled array based on the window size
        $size = match ($windowId) {
            self::WINDOW_INVENTORY => 36,
            self::WINDOW_ARMOR => 4,
            self::WINDOW_OFFHAND => 1,
            default => 0,
        };
        self::send($s, $sock, $windowId, array_fill(0, $size, null));
    }

    /**
     * @param array<int, ItemStack|null> $items
     */
    public static function send(Session $s, Socket $sock, int $windowId, array $items): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt($windowId);
        // windowId
        // Bedrock expects the full window size for the count
        $payload .= Binary::writeVarInt(\count($items));
        foreach ($items as $slot => $itemStack) {
            if ($itemStack === null) {
                // Air / empty slot
                $payload .= McpeBinary::writeSignedVarInt(0);
            } else {
                $payload .= self::writeItemStackWrapper($itemStack);
            }
        }
        // FullContainerName: containerId (varuint) + dynamicContainerId (optional LE uint32)
        $payload .= Binary::writeVarInt($windowId);
        // containerId
        $payload .= Binary::writeBool(false);
        // dynamicContainerId absent
        // storage item (empty)
        $payload .= McpeBinary::writeSignedVarInt(0);
        self::sendBatch(ProtocolInfo::INVENTORY_CONTENT_PACKET, $payload, $s, $sock);
    }

    private static function writeItemStackWrapper(ItemStack $itemStack): string
    {
        $data = '';
        $data .= McpeBinary::writeSignedVarInt($itemStack->getNumericId());
        $data .= Binary::writeLShort($itemStack->count);
        $data .= Binary::writeLShort($itemStack->damage);
        // has NBT tag
        $data .= Binary::writeLShort(0);
        // canPlace count, canBreak count
        $data .= McpeBinary::writeSignedVarInt(0);
        $data .= McpeBinary::writeSignedVarInt(0);
        // blocking tick (for shields)
        $data .= Binary::writeLong(0);
        return $data;
    }
}
