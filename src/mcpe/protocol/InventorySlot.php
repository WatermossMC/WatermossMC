<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;

final class InventorySlot extends Packet
{
    public static function sendEmpty(Session $s, Socket $sock, int $windowId, int $slot): void
    {
        self::send($s, $sock, $windowId, $slot, 0, 0, 0);
    }

    public static function send(
        Session $s,
        Socket $sock,
        int $windowId,
        int $slot,
        int $itemId,
        int $count,
        int $damage
    ): void {
        $payload = '';
        $payload .= Binary::writeVarInt($windowId); // windowId
        $payload .= Binary::writeVarInt($slot);     // inventorySlot

        // FullContainerName
        $payload .= Binary::writeVarInt($windowId); // containerId
        $payload .= Binary::writeBool(false);        // dynamicContainerId absent

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
        $data .= Binary::writeLShort(0); // NBT absent
        $data .= McpeBinary::writeSignedVarInt(0); // canPlace
        $data .= McpeBinary::writeSignedVarInt(0); // canBreak
        $data .= Binary::writeLong(0);  // blocking tick

        return $data;
    }
}
