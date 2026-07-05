<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\mcpe\network\Session;

final class InventoryContent extends Packet
{
    // Window IDs
    public const WINDOW_INVENTORY = 0;
    public const WINDOW_ARMOR = 6;
    public const WINDOW_OFFHAND = 119;

    public static function sendEmpty(Session $s, Socket $sock, int $windowId): void
    {
        self::send($s, $sock, $windowId, []);
    }

    /**
     * @param array<int, array{id: int, count: int, damage: int}> $items
     */
    public static function send(Session $s, Socket $sock, int $windowId, array $items): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt($windowId);     // windowId
        $payload .= Binary::writeVarInt(\count($items)); // item count

        foreach ($items as $item) {
            $payload .= self::writeItemStackWrapper($item['id'], $item['count'], $item['damage']);
        }

        // FullContainerName: containerId (varuint) + dynamicContainerId (optional LE uint32)
        $payload .= Binary::writeVarInt($windowId); // containerId
        $payload .= Binary::writeBool(false);        // dynamicContainerId absent

        // storage item (empty)
        $payload .= self::writeItemStackWrapper(0, 0, 0);

        self::sendBatch(ProtocolInfo::INVENTORY_CONTENT_PACKET, $payload, $s, $sock);
    }

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
