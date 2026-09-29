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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\binary\McpeBinary;
use watermossmc\network\mcpe\protocol\ProtocolInfo;

final class LevelSettings
{
    public static function write(
        int $seed,
        array $spawn,
        int $gameMode
    ): string {
        $payload = '';

        $payload .= McpeBinary::writeLLong($seed);
        $payload .= McpeBinary::writeLShort(0);
        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeSignedVarInt(0);

        $payload .= McpeBinary::writeSignedVarInt(1);
        $payload .= McpeBinary::writeSignedVarInt($gameMode);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeSignedVarInt(1);

        $payload .= McpeBinary::writeSignedVarInt((int) $spawn['x']);
        $payload .= McpeBinary::writeSignedVarInt((int) $spawn['y']);
        $payload .= McpeBinary::writeSignedVarInt((int) $spawn['z']);

        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeSignedVarInt(0);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeSignedVarInt(-1);
        $payload .= McpeBinary::writeUnsignedVarInt(0);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeFloat(0.0);
        $payload .= McpeBinary::writeFloat(0.0);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(true);
        $payload .= McpeBinary::writeBool(true);
        $payload .= McpeBinary::writeSignedVarInt(0);
        $payload .= McpeBinary::writeSignedVarInt(0);
        $payload .= McpeBinary::writeBool(true);
        $payload .= McpeBinary::writeBool(false);

        $payload .= McpeBinary::writeUnsignedVarInt(0);

        $payload .= McpeBinary::writeLInt(0);
        $payload .= McpeBinary::writeBool(false);

        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);

        $payload .= McpeBinary::writeByte(1);
        $payload .= McpeBinary::writeLInt(4);

        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeBool(false);

        $payload .= McpeBinary::writeString(
            ProtocolInfo::MINECRAFT_VERSION_NETWORK
        );

        $payload .= McpeBinary::writeLInt(0);
        $payload .= McpeBinary::writeLInt(0);
        $payload .= McpeBinary::writeBool(true);

        $payload .= McpeBinary::writeString('');
        $payload .= McpeBinary::writeString('');

        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeByte(0);
        $payload .= McpeBinary::writeBool(false);
        $payload .= McpeBinary::writeSignedVarInt(0);
        $payload .= McpeBinary::writeBool(false);

        return $payload;
    }
}
