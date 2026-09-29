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

final class MoveActorDelta
{
    public static function send(
        Session $s,
        Socket $sock,
        int $actorRuntimeId,
        ?float $x,
        ?float $y,
        ?float $z,
        ?float $pitch,
        ?float $yaw,
        ?float $headYaw,
        bool $onGround = false,
        bool $teleport = false,
        bool $forceMoveLocalEntity = false,
        bool $forceCompletion = false,
        int $ticks = 0
    ): void {
        $p = McpeBinary::writeUnsignedVarLong($actorRuntimeId);
        foreach ([$x, $y, $z] as $coord) {
            $p .= McpeBinary::writeBool($coord !== null);
            if ($coord !== null) {
                $p .= McpeBinary::writeFloat($coord);
            }
        }
        foreach ([$pitch, $yaw, $headYaw] as $rotation) {
            $p .= McpeBinary::writeBool($rotation !== null);
            if ($rotation !== null) {
                $p .= McpeBinary::writeByte(MoveActorAbsolute::rotationToByte($rotation));
            }
        }
        $p .= McpeBinary::writeBool($onGround);
        $p .= McpeBinary::writeBool($teleport);
        $p .= McpeBinary::writeBool($forceMoveLocalEntity);
        $p .= McpeBinary::writeBool($forceCompletion);
        $p .= McpeBinary::writeUnsignedVarLong($ticks);
        Packet::sendBatch(ProtocolInfo::MOVE_ACTOR_DELTA_PACKET, $p, $s, $sock);
    }
}
