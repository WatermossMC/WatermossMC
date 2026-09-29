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
use watermossmc\player\Player;

final class AddPlayer extends Packet
{
    private const PERMISSION_MEMBER = 1;
    private const COMMAND_PERMISSION_NORMAL = 0;
    private const LAYER_BASE = 1;
    private const DEVICE_OS_UNKNOWN = -1;

    public static function send(Session $s, Socket $sock, Player $target): void
    {
        $pos = $target->getPosition();
        $rot = $target->getRotation();

        $p = McpeBinary::writeUUID($target->getUuid());
        $p .= McpeBinary::writeString($target->getUsername());
        $p .= McpeBinary::writeUnsignedVarLong($target->getRuntimeId());
        $p .= McpeBinary::writeString('');

        $p .= McpeBinary::writeFloat($pos['x']);
        $p .= McpeBinary::writeFloat($pos['y']);
        $p .= McpeBinary::writeFloat($pos['z']);

        $p .= McpeBinary::writeFloat(0.0);
        $p .= McpeBinary::writeFloat(0.0);
        $p .= McpeBinary::writeFloat(0.0);

        $p .= McpeBinary::writeFloat($rot['pitch']);
        $p .= McpeBinary::writeFloat($rot['yaw']);
        $p .= McpeBinary::writeFloat($target->headYaw);

        $p .= self::writeEmptyItem();
        $p .= McpeBinary::writeSignedVarInt($target->getGameMode());

        $p .= McpeBinary::writeUnsignedVarInt(0);
        $p .= McpeBinary::writeUnsignedVarInt(0);
        $p .= McpeBinary::writeUnsignedVarInt(0);

        $p .= self::writeAbilities($target);

        $p .= McpeBinary::writeUnsignedVarInt(0);
        $p .= McpeBinary::writeString('');
        $p .= McpeBinary::writeLInt(self::DEVICE_OS_UNKNOWN);

        self::sendBatch(ProtocolInfo::ADD_PLAYER_PACKET, $p, $s, $sock);
    }

    private static function writeEmptyItem(): string
    {
        return McpeBinary::writeLShort(0)
            . McpeBinary::writeLShort(0)
            . McpeBinary::writeUnsignedVarInt(0)
            . McpeBinary::writeBool(false)
            . McpeBinary::writeUnsignedVarInt(0)
            . McpeBinary::writeString('');
    }

    private static function writeAbilities(Player $target): string
    {
        $a = McpeBinary::writeLLong($target->getRuntimeId());
        $a .= McpeBinary::writeByte(self::PERMISSION_MEMBER);
        $a .= McpeBinary::writeByte(self::COMMAND_PERMISSION_NORMAL);
        $a .= McpeBinary::writeByte(0);
        return $a;
    }
}
