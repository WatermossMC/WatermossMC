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

declare (strict_types=1);

namespace watermossmc\mcpe\protocol;

use Socket;
use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;

final class CraftingData extends Packet
{
    public static function sendEmpty(Session $s, Socket $sock): void
    {
        $payload = '';
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);
        $payload .= Binary::writeVarInt(0);
        // recipesWithTypeIds count
        $payload .= Binary::writeVarInt(0);
        // potionTypeRecipes count
        $payload .= Binary::writeVarInt(0);
        // potionContainerRecipes count
        $payload .= Binary::writeVarInt(0);
        // materialReducerRecipes count
        $payload .= Binary::writeBool(true);
        // cleanRecipes
        self::sendBatch(ProtocolInfo::CRAFTING_DATA_PACKET, $payload, $s, $sock);
    }
}
