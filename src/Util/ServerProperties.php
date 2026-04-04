<?php

declare(strict_types=1);

namespace WatermossMC\Util;

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
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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
            if (count($parts) !== 2) {
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
        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value) || $value === null) {
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

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value !== 0;
        }

        if (!is_scalar($value) && $value !== null) {
            return $default;
        }

        $normalized = strtolower((string) $value);
        return in_array($normalized, ['true', 'yes', 'on', '1'], true);
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
