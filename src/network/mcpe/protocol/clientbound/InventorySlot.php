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
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\mcpe\protocol\types\inventory\FullContainerName;
use watermossmc\network\mcpe\protocol\types\ItemStackDescriptor;
use watermossmc\network\Session;

final class InventorySlot extends Packet
{
    public static function sendEmpty(Session $s, Socket $sock, int $windowId, int $slot): void
    {
        self::send($s, $sock, $windowId, $slot, null, 0);
    }

    public static function send(Session $s, Socket $sock, int $windowId, int $slot, ?ItemStack $item, int $stackId): void
    {
        $payload = McpeBinary::writeUnsignedVarInt($windowId);
        $payload .= McpeBinary::writeUnsignedVarInt($slot);
        $payload .= McpeBinary::writeBool(true);
        $payload .= (new FullContainerName($windowId))->write();
        $payload .= McpeBinary::writeBool(false); // storage item absent
        $descriptor = $item === null ? ItemStackDescriptor::air() : $item->toDescriptor($stackId);
        $payload .= $descriptor->write();
        self::sendBatch(ProtocolInfo::INVENTORY_SLOT_PACKET, $payload, $s, $sock);
    }
}
