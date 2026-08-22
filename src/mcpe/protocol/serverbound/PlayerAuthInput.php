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

namespace watermossmc\mcpe\protocol\serverbound;

use watermossmc\binary\Binary;
use watermossmc\mcpe\network\Session;
use watermossmc\mcpe\protocol\Packet;
use watermossmc\mcpe\protocol\types\PlayerAuthInputFlags;
use watermossmc\player\PlayerManager;

final class PlayerAuthInput extends Packet
{
    public static function handle(string $p, Session $s): void
    {
        $o = 1;

        $pitch = Binary::readFloat($p, $o);
        $o += 4;
        $yaw = Binary::readFloat($p, $o);
        $o += 4;

        $x = Binary::readFloat($p, $o);
        $o += 4;
        $y = Binary::readFloat($p, $o);
        $o += 4;
        $z = Binary::readFloat($p, $o);
        $o += 4;

        $moveX = Binary::readFloat($p, $o);
        $o += 4;
        $moveZ = Binary::readFloat($p, $o);
        $o += 4;

        $headYaw = Binary::readFloat($p, $o);
        $o += 4;


        $flags = Binary::readBitSet($p, $o, 65);

        Binary::readVarInt($p, $o);
        Binary::readVarInt($p, $o);
        Binary::readVarInt($p, $o);

        Binary::readVector2($p, $o);
        $tick = Binary::readVarLong($p, $o);

        Binary::readVector3($p, $o);

        if ($flags[PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION]) {
            Binary::skipItemInteractionData($p, $o);
        }
        if ($flags[PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST]) {
            Binary::skipItemStackRequest($p, $o);
        }
        if ($flags[PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS]) {
            Binary::skipBlockActions($p, $o);
        }
        if ($flags[PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE]) {
            Binary::skipVehicleInfo($p, $o);
        }

        $analogX = Binary::readFloat($p, $o);
        $o += 4;
        $analogZ = Binary::readFloat($p, $o);
        $o += 4;

        Binary::readVector3($p, $o);
        Binary::readVector2($p, $o);

        $player = PlayerManager::get($s);
        if ($player === null) {
            return;
        }

        $player->pendingMove = [
            'x' => $x,
            'y' => $y,
            'z' => $z,
            'yaw' => $yaw,
            'pitch' => $pitch,
            'headYaw' => $headYaw,
            'tick' => $tick,
        ];
    }
}
