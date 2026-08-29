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

namespace watermossmc\network\mcpe\protocol\serverbound;

use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\Packet;

final class Respawn extends Packet
{
    public const SEARCHING_FOR_SPAWN = 0;
    public const READY_TO_SPAWN = 1;
    public const CLIENT_READY_TO_SPAWN = 2;

    /**
     * @return array{x: float, y: float, z: float, respawnState: int, actorRuntimeId: int}
     */
    public static function read(string $p, int &$o): array
    {
        $x = McpeBinary::readFloat($p, $o);
        $y = McpeBinary::readFloat($p, $o);
        $z = McpeBinary::readFloat($p, $o);
        $respawnState = McpeBinary::readByte($p, $o);
        $actorRuntimeId = McpeBinary::readSignedVarLong($p, $o);

        return [
            'x' => $x,
            'y' => $y,
            'z' => $z,
            'respawnState' => $respawnState,
            'actorRuntimeId' => $actorRuntimeId,
        ];
    }
}
