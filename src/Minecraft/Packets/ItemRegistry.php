<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\Packets;

use Socket;
use WatermossMC\Binary\Binary;
use WatermossMC\Binary\McpeBinary;
use WatermossMC\Minecraft\Data\ItemTypeList;
use WatermossMC\Minecraft\NBT\NBT;
use WatermossMC\Network\Session;
use WatermossMC\Util\Logger;

/**
 * Item entries are loaded from ItemTypeList — regenerate that class
 * whenever you update the BedrockData (PMMP) required_item_list.json.
 */
final class ItemRegistry extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $items = ItemTypeList::getEntries();

        $payload  = '';
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
