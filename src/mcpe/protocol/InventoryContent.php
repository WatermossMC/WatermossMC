<?php

<<<<<<< HEAD
=======
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

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

<<<<<<< HEAD
use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
=======
use function count;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\inventory\ItemStack;
>>>>>>> 866a1c0 (...)
use watermossmc\mcpe\network\Session;

final class InventoryContent extends Packet
{
    // Window IDs
    public const WINDOW_INVENTORY = 0;
    public const WINDOW_ARMOR = 6;
    public const WINDOW_OFFHAND = 119;

<<<<<<< HEAD
    public static function sendEmpty(Session $s, Socket $sock, int $windowId): void
    {
        self::send($s, $sock, $windowId, []);
    }

    /**
     * @param array<int, array{id: int, count: int, damage: int}> $items
=======
    /**
     * @param array<int, ItemStack|null> $items
>>>>>>> 866a1c0 (...)
     */
    public static function send(Session $s, Socket $sock, int $windowId, array $items): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt($windowId);     // windowId
<<<<<<< HEAD
        $payload .= Binary::writeVarInt(\count($items)); // item count

        foreach ($items as $item) {
            $payload .= self::writeItemStackWrapper($item['id'], $item['count'], $item['damage']);
=======

        // Bedrock expects the full window size for the count
        $payload .= Binary::writeVarInt(\count($items));

        foreach ($items as $slot => $itemStack) {
            if ($itemStack === null) {
                // Air / empty slot
                $payload .= McpeBinary::writeSignedVarInt(0);
            } else {
                $payload .= self::writeItemStackWrapper($itemStack);
            }
>>>>>>> 866a1c0 (...)
        }

        // FullContainerName: containerId (varuint) + dynamicContainerId (optional LE uint32)
        $payload .= Binary::writeVarInt($windowId); // containerId
        $payload .= Binary::writeBool(false);        // dynamicContainerId absent

        // storage item (empty)
<<<<<<< HEAD
        $payload .= self::writeItemStackWrapper(0, 0, 0);
=======
        $payload .= McpeBinary::writeSignedVarInt(0);
>>>>>>> 866a1c0 (...)

        self::sendBatch(ProtocolInfo::INVENTORY_CONTENT_PACKET, $payload, $s, $sock);
    }

<<<<<<< HEAD
    private static function writeItemStackWrapper(int $id, int $count, int $damage): string
    {
        if ($id === 0) {
            // Air / empty slot
            return McpeBinary::writeSignedVarInt(0);
        }

        $data = '';
        $data .= McpeBinary::writeSignedVarInt($id);
        $data .= Binary::writeLShort($count);
        $data .= Binary::writeLShort($damage);
=======
    private static function writeItemStackWrapper(ItemStack $itemStack): string
    {
        $data = '';
        $data .= McpeBinary::writeSignedVarInt($itemStack->getNumericId());
        $data .= Binary::writeLShort($itemStack->count);
        $data .= Binary::writeLShort($itemStack->damage);
>>>>>>> 866a1c0 (...)

        // has NBT tag
        $data .= Binary::writeLShort(0);

        // canPlace count, canBreak count
        $data .= McpeBinary::writeSignedVarInt(0);
        $data .= McpeBinary::writeSignedVarInt(0);

        // blocking tick (for shields)
        $data .= Binary::writeLong(0);

        return $data;
    }
<<<<<<< HEAD
=======

    public static function sendEmpty(Session $s, Socket $sock, int $windowId): void
    {
        // Create a null-filled array based on the window size
        $size = match($windowId) {
            self::WINDOW_INVENTORY => 36,
            self::WINDOW_ARMOR => 4,
            self::WINDOW_OFFHAND => 1,
            default => 0
        };
        self::send($s, $sock, $windowId, array_fill(0, $size, null));
    }
>>>>>>> 866a1c0 (...)
}
