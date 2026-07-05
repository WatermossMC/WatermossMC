<?php

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
