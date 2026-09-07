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

namespace watermossmc\plugin;

use RuntimeException;

final class PluginConfig
{
    /** @var array<string, mixed> */
    private array $values;

    /** @var array<string, mixed> */
    private array $defaults;

    /**
     * @param array<string, mixed> $defaults
     */
    public function __construct(private readonly string $path, array $defaults = [])
    {
        $this->defaults = $defaults;
        $this->values = $defaults;
        $this->reload();
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function getString(string $key, string $default = ''): string
    {
        $value = $this->get($key, $default);
        return is_scalar($value) ? (string) $value : $default;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key, $default);
        return is_int($value) || is_float($value) || is_string($value) && is_numeric($value) ? (int) $value : $default;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return $value !== 0;
        }
        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
        }
        return $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->values[$key]);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->values;
    }

    public function reload(): void
    {
        if (!is_file($this->path)) {
            return;
        }

        $json = file_get_contents($this->path);
        if ($json === false) {
            throw new RuntimeException('Unable to read plugin config: ' . $this->path);
        }
        $data = json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new RuntimeException('Plugin config root must be an object: ' . $this->path);
        }
        $this->values = array_replace($this->defaults, $data);
    }

    public function save(): void
    {
        $directory = dirname($this->path);
        if (!is_dir($directory) && !mkdir($directory, 0o777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create plugin config directory: ' . $directory);
        }
        $json = json_encode($this->values, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR) . "\n";
        if (file_put_contents($this->path, $json, \LOCK_EX) === false) {
            throw new RuntimeException('Unable to save plugin config: ' . $this->path);
        }
    }
}
