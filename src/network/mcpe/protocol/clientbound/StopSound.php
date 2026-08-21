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
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\ProtocolInfo;
use watermossmc\network\Session;

final class StopSound extends Packet
{
    public static function send(Session $s, Socket $sock, string $soundName, bool $stopAll, bool $stopLegacyMusic): void
    {
<<<<<<< HEAD:src/network/mcpe/protocol/clientbound/StopSound.php
        $p = McpeBinary::writeString($soundName) .
             McpeBinary::writeBool($stopAll) .
             McpeBinary::writeBool($stopLegacyMusic);
        self::sendBatch(ProtocolInfo::STOP_SOUND_PACKET, $p, $s, $sock);
=======
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
>>>>>>> c945639 (...):src/mcpe/protocol/CraftingData.php
    }
}
