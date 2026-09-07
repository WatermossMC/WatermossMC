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

use watermossmc\binary\Binary;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\types\MovePlayerMode;
use watermossmc\network\Session;
use watermossmc\player\PlayerManager;

final class MovePlayer extends Packet
{
    /**
     * @return array{x: float, y: float, z: float, pitch: float, yaw: float, headYaw: float, onGround: bool}
     */
    public static function read(string $p, int &$o): array
    {
        $o++;
        // skip PID
        Binary::readLong($p, $o);
        // skip runtimeId
        $x = Binary::readFloat($p, $o);
        $y = Binary::readFloat($p, $o);
        $z = Binary::readFloat($p, $o);
        $pitch = Binary::readFloat($p, $o);
        $yaw = Binary::readFloat($p, $o);
        $headYaw = Binary::readFloat($p, $o);
        $mode = Binary::readByte($p, $o);
        $onGround = Binary::readBool($p, $o);
        Binary::readLong($p, $o);
        // skip something
        if ($mode === MovePlayerMode::TELEPORT) {
            Binary::readInt($p, $o);
            Binary::readInt($p, $o);
        }
        Binary::readVarLong($p, $o);
        // skip something
        return ['x' => $x, 'y' => $y, 'z' => $z, 'pitch' => $pitch, 'yaw' => $yaw, 'headYaw' => $headYaw, 'onGround' => $onGround];
    }

    public static function handle(string $p, Session $s): void
    {
        $o = 1;
        Binary::readLong($p, $o);
        $x = Binary::readFloat($p, $o);
        $y = Binary::readFloat($p, $o);
        $z = Binary::readFloat($p, $o);
        $pitch = Binary::readFloat($p, $o);
        $yaw = Binary::readFloat($p, $o);
        $headYaw = Binary::readFloat($p, $o);
        $mode = Binary::readByte($p, $o);
        $onGround = Binary::readBool($p, $o);
        Binary::readLong($p, $o);
        if ($mode === MovePlayerMode::TELEPORT) {
            Binary::readInt($p, $o);
            Binary::readInt($p, $o);
        }
        Binary::readVarLong($p, $o);
        $player = PlayerManager::get($s);
        if ($player === null) {
            return;
        }
        $player->pendingMove = ['x' => $x, 'y' => $y, 'z' => $z, 'yaw' => $yaw, 'pitch' => $pitch, 'headYaw' => $headYaw, 'onGround' => $onGround];
    }
}
