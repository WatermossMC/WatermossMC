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

final class PlayerAction extends Packet
{
    public const ACTION_START_BREAK = 0;
    public const ACTION_ABORT_BREAK = 1;
    public const ACTION_STOP_BREAK = 2;
    public const ACTION_GET_UPDATED_BLOCK = 3;
    public const ACTION_DROP_ITEM = 4;
    public const ACTION_START_SLEEPING = 5;
    public const ACTION_STOP_SLEEPING = 6;
    public const ACTION_RESPAWN = 7;
    public const ACTION_JUMP = 8;
    public const ACTION_START_SPRINT = 9;
    public const ACTION_STOP_SPRINT = 10;
    public const ACTION_START_SNEAK = 11;
    public const ACTION_STOP_SNEAK = 12;
    public const ACTION_CREATIVE_PLAYER_DESTRUCT_BLOCK = 13;
    public const ACTION_DIMENSION_CHANGE_ACK = 14;
    public const ACTION_START_GLIDE = 15;
    public const ACTION_STOP_GLIDE = 16;
    public const ACTION_BUILD_DENIED = 17;
    public const ACTION_CONTINUE_BREAK = 18;
    public const ACTION_CHANGE_SKIN = 19;
    public const ACTION_SET_ENCHANTMENT_SEED = 20;
    public const ACTION_START_SWIMMING = 21;
    public const ACTION_STOP_SWIMMING = 22;
    public const ACTION_START_SPIN_ATTACK = 23;
    public const ACTION_STOP_SPIN_ATTACK = 24;
    public const ACTION_INTERACT_BLOCK = 25;
    public const ACTION_PREDICT_DESTROY_BLOCK = 26;
    public const ACTION_CONTINUE_DESTROY_BLOCK = 27;
    public const ACTION_START_ITEM_USE_ON = 28;
    public const ACTION_STOP_ITEM_USE_ON = 29;
    public const ACTION_HANDLED_TELEPORT = 30;

    /**
     * @return array{actorRuntimeId: int, action: int, blockX: int, blockY: int, blockZ: int, face: int}
     */
    public static function read(string $p, int &$o): array
    {
        $actorRuntimeId = McpeBinary::readSignedVarLong($p, $o);
        $action = McpeBinary::readSignedVarInt($p, $o);
        $blockX = McpeBinary::readVarInt($p, $o);
        $blockY = McpeBinary::readUnsignedVarInt($p, $o);
        $blockZ = McpeBinary::readVarInt($p, $o);
        $face = McpeBinary::readSignedVarInt($p, $o);

        return [
            'actorRuntimeId' => $actorRuntimeId,
            'action' => $action,
            'blockX' => $blockX,
            'blockY' => $blockY,
            'blockZ' => $blockZ,
            'face' => $face,
        ];
    }
}
