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

final class LevelSoundEvent
{
    private const MAX_STRING = 256;

    public static function read(string $p, int &$o): array
    {
        $sound = McpeBinary::readString($p, $o);
        $x = McpeBinary::readFloat($p, $o);
        $y = McpeBinary::readFloat($p, $o);
        $z = McpeBinary::readFloat($p, $o);
        $extraData = McpeBinary::readSignedVarInt($p, $o);
        $entityType = McpeBinary::readString($p, $o);
        $isBabyMob = McpeBinary::readBool($p, $o);
        $disableRelativeVolume = McpeBinary::readBool($p, $o);
        $actorUniqueId = McpeBinary::readLLong($p, $o);
        if (McpeBinary::readBool($p, $o)) {
            McpeBinary::readFloat($p, $o);
            McpeBinary::readFloat($p, $o);
            McpeBinary::readFloat($p, $o);
        }

        if (\strlen($sound) > self::MAX_STRING || \strlen($entityType) > self::MAX_STRING) {
            throw new RuntimeException('LevelSoundEvent string too long');
        }

        return [
            'sound' => $sound, 'x' => $x, 'y' => $y, 'z' => $z,
            'extraData' => $extraData, 'entityType' => $entityType,
            'isBabyMob' => $isBabyMob, 'disableRelativeVolume' => $disableRelativeVolume,
            'actorUniqueId' => $actorUniqueId,
        ];
    }
}
