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
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Session;

final class CraftingData extends Packet
{
    public static function send(Session $s, Socket $sock): void
    {
        $payload = '';

        // Recipes (Shapeless, Shaped, Furnace, etc.)
        // 1. shapeless recipe count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // 2. shaped recipe count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // 3. furnace recipe count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // 4. furnace aux recipe count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // 5. pps / multi recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // 6. material reducer recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // 7. smithing transform recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // 8. smithing trim recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // recipes with type IDs count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // potion type recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // potion container recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // material reducer recipes count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        // cleanRecipes (bool)
        $payload .= Binary::writeBool(true);

        self::sendBatch(ProtocolInfo::CRAFTING_DATA_PACKET, $payload, $s, $sock);
    }
}
