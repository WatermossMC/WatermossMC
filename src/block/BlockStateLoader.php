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

namespace watermossmc\block;

use RuntimeException;
use watermossmc\nbt\NBT;

final class BlockStateLoader
{
    public static function load(string $file): RuntimeIdMap
    {
        if (!is_file($file)) {
            throw new RuntimeException(
                "Block state data not found: {$file}"
            );
        }

        $data = file_get_contents($file);

        if ($data === false) {
            throw new RuntimeException(
                "Failed to read block state data: {$file}"
            );
        }

        return self::loadString($data);
    }

    public static function loadString(string $data): RuntimeIdMap
    {
        $nbt = NBT::parseMultipleNetwork($data);

        if (!is_array($nbt)) {
            throw new RuntimeException(
                'Invalid canonical block state data'
            );
        }

        $map = new RuntimeIdMap();

        foreach ($nbt as $runtimeId => $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $name = $entry['name'] ?? null;
            $states = $entry['states'] ?? [];

            if (!is_string($name)) {
                continue;
            }

            if (!is_array($states)) {
                $states = [];
            }

            $state = new BlockState(
                $name,
                $states
            );

            $map->register(
                $state,
                (int) $runtimeId
            );
        }

        return $map;
    }
}
