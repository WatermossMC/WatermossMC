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

final class SetSpawnPosition extends Packet
{
    public const SPAWN_TYPE_PLAYER_SPAWN = 0;
    public const SPAWN_TYPE_WORLD_SPAWN = 1;

    public static function send(Session $s, Socket $sock, int $x = 0, int $y = 64, int $z = 0): void
    {
        $p = McpeBinary::writeVarInt(self::SPAWN_TYPE_WORLD_SPAWN);

        $p .= McpeBinary::writeSignedVarInt($x); // X — signed
        $p .= McpeBinary::writeVarInt($y);       // Y — unsigned
        $p .= McpeBinary::writeSignedVarInt($z); // Z — signed

        $p .= McpeBinary::writeSignedVarInt($x);
        $p .= McpeBinary::writeVarInt($y);
        $p .= McpeBinary::writeSignedVarInt($z);

        $p .= McpeBinary::writeVarInt(0);        // dimensionId
        $p .= Binary::writeBool(false);          // spawnForced

        self::sendBatch(ProtocolInfo::SET_SPAWN_POSITION_PACKET, $p, $s, $sock);
    }
}
