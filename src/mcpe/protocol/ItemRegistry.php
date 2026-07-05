<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\data\ItemTypeList;
use watermossmc\mcpe\network\Session;
use watermossmc\nbt\NBT;
use watermossmc\util\Logger;

/**
 * Item entries are loaded from ItemTypeList — regenerate that class
 * whenever you update the BedrockData (PMMP) required_item_list.json.
 */
final class ItemRegistry extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $items = ItemTypeList::getEntries();

        $payload = '';
        $payload .= Binary::writeVarInt(\count($items));

        foreach ($items as $item) {
            $payload .= McpeBinary::writeString($item['stringId']);       // string id
            $payload .= Binary::writeLShort($item['numericId']);          // numeric id (LE signed short)
            $payload .= Binary::writeBool($item['componentBased']);       // is component based
            $payload .= McpeBinary::writeSignedVarInt($item['version']);  // version
            $payload .= NBT::compound([]);                                // component NBT (empty compound)
        }

        $hex = bin2hex($payload);
        Logger::debug("ItemRegistry payload hex: " . $hex);

        self::sendBatch(ProtocolInfo::ITEM_REGISTRY_PACKET, $payload, $s, $sock);
    }
}
