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

final class Respawn extends Packet
{
    public const SEARCHING_FOR_SPAWN = 0;
    public const READY_TO_SPAWN = 1;
    public const CLIENT_READY_TO_SPAWN = 2;

    public static function send(Session $s, Socket $sock, float $x, float $y, float $z, int $respawnState, int $actorRuntimeId): void
    {
        $p = McpeBinary::writeFloat($x) .
             McpeBinary::writeFloat($y) .
             McpeBinary::writeFloat($z) .
             McpeBinary::writeByte($respawnState) .
             McpeBinary::writeSignedVarLong($actorRuntimeId);
        self::sendBatch(ProtocolInfo::RESPAWN_PACKET, $p, $s, $sock);
    }
}
