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

use RuntimeException;
use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\Packet;
use watermossmc\network\mcpe\protocol\types\PlayerAuthInputFlags;
use watermossmc\network\Session;
use watermossmc\player\PlayerManager;

final class PlayerAuthInput extends Packet
{
    public static function handle(string $p, Session $s): void
    {
        $o = 1;

        $pitch = McpeBinary::readFloat($p, $o);
        $yaw = McpeBinary::readFloat($p, $o);

        $x = McpeBinary::readFloat($p, $o);
        $y = McpeBinary::readFloat($p, $o);
        $z = McpeBinary::readFloat($p, $o);

        $moveX = McpeBinary::readFloat($p, $o);
        $moveZ = McpeBinary::readFloat($p, $o);

        $headYaw = McpeBinary::readFloat($p, $o);

        $flags = array_fill(
            0,
            PlayerAuthInputFlags::NUMBER_OF_FLAGS,
            false
        );

        $flagCount = McpeBinary::readUnsignedVarInt($p, $o);

        for ($i = 0; $i < $flagCount; ++$i) {
            $flag = McpeBinary::readSignedVarInt($p, $o);

            if (
                $flag < 0 ||
                $flag >= PlayerAuthInputFlags::NUMBER_OF_FLAGS
            ) {
                throw new RuntimeException(
                    "Unknown input flag {$flag}"
                );
            }

            $flags[$flag] = true;
        }

        $inputMode = McpeBinary::readUnsignedVarInt($p, $o);
        $playMode = McpeBinary::readUnsignedVarInt($p, $o);
        $interactionMode = McpeBinary::readSignedVarInt($p, $o);

        McpeBinary::readFloat($p, $o);
        McpeBinary::readFloat($p, $o);

        $tick = McpeBinary::readUnsignedVarLong($p, $o);

        McpeBinary::readFloat($p, $o);
        McpeBinary::readFloat($p, $o);
        McpeBinary::readFloat($p, $o);

        if (
            $flags[PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION]
            ?? false
        ) {
            Binary::skipItemInteractionData($p, $o);
        }

        if (
            $flags[PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST]
            ?? false
        ) {
            Binary::skipItemStackRequest($p, $o);
        }

        if (
            $flags[PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS]
            ?? false
        ) {
            Binary::skipBlockActions($p, $o);
        }

        if (
            $flags[PlayerAuthInputFlags::IN_CLIENT_PREDICTED_VEHICLE]
            ?? false
        ) {
            Binary::skipVehicleInfo($p, $o);
        }

        McpeBinary::readFloat($p, $o);
        McpeBinary::readFloat($p, $o);

        McpeBinary::readFloat($p, $o);
        McpeBinary::readFloat($p, $o);
        McpeBinary::readFloat($p, $o);

        McpeBinary::readFloat($p, $o);
        McpeBinary::readFloat($p, $o);

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
