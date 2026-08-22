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

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\ProtocolInfo;

final class InventorySlot extends Packet
{
    public static function sendEmpty(Session $s, Socket $sock, int $windowId, int $slot): void
    {
        self::send($s, $sock, $windowId, $slot, 0, 0, 0);
    }

    public static function send(Session $s, Socket $sock, int $windowId, int $slot, int $itemId, int $count, int $damage): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt($windowId);
        // windowId
        $payload .= Binary::writeVarInt($slot);
        // inventorySlot
        // FullContainerName
        $payload .= Binary::writeVarInt($windowId);
        // containerId
        $payload .= Binary::writeBool(false);
        // dynamicContainerId absent
        // storage item (empty)
        $payload .= McpeBinary::writeSignedVarInt(0);
        // the actual item
        $payload .= self::writeItemStackWrapper($itemId, $count, $damage);
        self::sendBatch(ProtocolInfo::INVENTORY_SLOT_PACKET, $payload, $s, $sock);
    }

    private static function writeItemStackWrapper(int $id, int $count, int $damage): string
    {
        if ($id === 0) {
            return McpeBinary::writeSignedVarInt(0);
        }
        $data = '';
        $data .= McpeBinary::writeSignedVarInt($id);
        $data .= Binary::writeLShort($count);
        $data .= Binary::writeLShort($damage);
        $data .= Binary::writeLShort(0);
        // NBT absent
        $data .= McpeBinary::writeSignedVarInt(0);
        // canPlace
        $data .= McpeBinary::writeSignedVarInt(0);
        // canBreak
        $data .= Binary::writeLong(0);
        // blocking tick
        return $data;
    }
}
