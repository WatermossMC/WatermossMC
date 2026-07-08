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

namespace watermossmc\util;

final class Config
{
    private static ?ServerProperties $properties = null;

    public static function load(string $path): void
    {
        self::$properties = ServerProperties::loadFile($path);
    }

    public static function has(string $key): bool
    {
        return self::$properties !== null && self::$properties->has($key);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$properties === null) {
            return $default;
        }

        if (self::$properties->has($key)) {
            return self::$properties->get($key, $default);
        }

        return $default;
    }

    public static function getString(string $key, string $default = ''): string
    {
        if (self::$properties === null) {
            return $default;
        }

        return self::$properties->getString($key, $default);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        if (self::$properties === null) {
            return $default;
        }

        return self::$properties->getInt($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        if (self::$properties === null) {
            return $default;
        }

        return self::$properties->getBool($key, $default);
    }
}
