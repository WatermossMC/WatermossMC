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

final class ServerProperties
{
    /** @var array<string, string> */
    private array $properties = [];

    /**
     * @param array<string, string> $properties
     */
    private function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    public static function loadFile(string $path): self
    {
        if (!is_file($path) || !is_readable($path)) {
            return new self([]);
        }

        $properties = [];
        $lines = file($path, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return new self([]);
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, '!')) {
                continue;
            }

            $delimiter = str_contains($line, '=') ? '=' : ':';
            $parts = explode($delimiter, $line, 2);
            if (\count($parts) !== 2) {
                continue;
            }

            $key = self::normalizeKey(trim($parts[0]));
            $value = trim($parts[1]);

            if ($key === '') {
                continue;
            }

            $properties[$key] = $value;
        }

        return new self($properties);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $normalized = self::normalizeKey($key);

        if (isset($this->properties[$normalized])) {
            return $this->properties[$normalized];
        }

        return $default;
    }

    public function has(string $key): bool
    {
        return isset($this->properties[self::normalizeKey($key)]);
    }

    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        if (\is_string($value)) {
            return $value;
        }

        if (\is_scalar($value) || $value === null) {
            return (string) $value;
        }

        return $default;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        return is_numeric($value) ? (int) $value : $default;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);

        if (\is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value !== 0;
        }

        if (!\is_scalar($value) && $value !== null) {
            return $default;
        }

        $normalized = strtolower((string) $value);
        return \in_array($normalized, ['true', 'yes', 'on', '1'], true);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->properties;
    }

    private static function normalizeKey(string $key): string
    {
        return strtolower(str_replace(['.', '-'], '_', trim($key)));
    }
}
