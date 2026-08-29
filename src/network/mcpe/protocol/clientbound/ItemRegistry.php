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
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\data\ItemTypeList;
use watermossmc\network\Session;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
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
            $payload .= McpeBinary::writeString($item['stringId']);
            // string id
            $payload .= Binary::writeLShort($item['numericId']);
            // numeric id (LE signed short)
            $payload .= Binary::writeBool($item['componentBased']);
            // is component based
            $payload .= McpeBinary::writeSignedVarInt($item['version']);
            // version
            $payload .= NBT::compound([]);
            // component NBT (empty compound)
        }
        $hex = bin2hex($payload);
        Logger::debug("ItemRegistry payload hex: " . $hex);
        self::sendBatch(ProtocolInfo::ITEM_REGISTRY_PACKET, $payload, $s, $sock);
    }
}
