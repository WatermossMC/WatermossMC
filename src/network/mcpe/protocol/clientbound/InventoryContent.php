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
use watermossmc\binary\McpeBinary;
use watermossmc\inventory\ItemStack;
use watermossmc\inventory\PlayerInventory;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\types\ContainerIds;
use watermossmc\network\mcpe\protocol\types\inventory\FullContainerName;
use watermossmc\network\mcpe\protocol\types\ItemStackDescriptor;
use watermossmc\network\Session;

final class InventoryContent extends Packet
{
    public const WINDOW_INVENTORY = ContainerIds::INVENTORY;
    public const WINDOW_ARMOR = ContainerIds::ARMOR;
    public const WINDOW_OFFHAND = ContainerIds::OFFHAND;

    public static function sendEmpty(Session $s, Socket $sock, int $windowId): void
    {
        $size = match ($windowId) {
            self::WINDOW_INVENTORY => 36,
            self::WINDOW_ARMOR => 4,
            self::WINDOW_OFFHAND => 1,
            default => 0,
        };
        self::send($s, $sock, $windowId, array_fill(0, $size, null), array_fill(0, $size, 0));
    }

    /**
     * @param array<int, ItemStack|null> $items
     * @param array<int, int> $stackIds parallel to $items; the network stack id of each slot (0 = none)
     */
    public static function send(Session $s, Socket $sock, int $windowId, array $items, array $stackIds = []): void
    {
        $payload = McpeBinary::writeUnsignedVarInt($windowId);
        $payload .= McpeBinary::writeUnsignedVarInt(\count($items));
        $i = 0;
        foreach ($items as $itemStack) {
            $descriptor = $itemStack === null
                ? ItemStackDescriptor::air()
                : $itemStack->toDescriptor($stackIds[$i] ?? 0);
            $payload .= $descriptor->write();
            $i++;
        }
        $payload .= (new FullContainerName($windowId))->write();
        $payload .= ItemStackDescriptor::air()->write(); // storage item, unused outside dynamic containers
        self::sendBatch(ProtocolInfo::INVENTORY_CONTENT_PACKET, $payload, $s, $sock);
    }

    public static function sendForInventory(Session $s, Socket $sock, int $windowId, PlayerInventory $inventory): void
    {
        $items = $inventory->getWindowItems($windowId);
        $stackIds = [];
        foreach (array_keys($items) as $slot) {
            $stackIds[] = $inventory->getOrAllocateStackId($windowId, $slot);
        }
        self::send($s, $sock, $windowId, $items, $stackIds);
    }
}
