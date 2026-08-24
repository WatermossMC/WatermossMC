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

use watermossmc\VersionInfo;

use RuntimeException;

final class PluginDescription
{
    /**
     * @param array<string, mixed> $extra
     */
    private function __construct(public readonly string $name, public readonly string $version, public readonly string $main, public readonly string $api, public readonly array $extra = []) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data, string $source): self
    {
        foreach (['name', 'version', 'main'] as $field) {
            if (!isset($data[$field]) || !\is_string($data[$field]) || trim($data[$field]) === '') {
                throw new RuntimeException("Invalid plugin manifest {$source}: missing {$field}");
            }
        }
        $api = isset($data['api']) && \is_string($data['api']) ? $data['api'] : VersionInfo::getApiVersion();
        return new self(trim($data['name']), trim($data['version']), trim($data['main']), trim($api), $data);
    }

    public function getDescription(): string
    {
        return $this->getString('description');
    }

    public function getWebsite(): string
    {
        return $this->getString('website');
    }

    public function getPrefix(): string
    {
        return $this->getString('prefix', $this->name);
    }

    /**
     * @return list<string>
     */
    public function getAuthors(): array
    {
        $author = $this->getString('author');
        return array_values(array_unique([...($author === '' ? [] : [$author]), ...$this->getStringList('authors')]));
    }

    /**
     * @return list<string>
     */
    public function getDepend(): array
    {
        return $this->getStringList('depend');
    }

    /**
     * @return list<string>
     */
    public function getSoftDepend(): array
    {
        return $this->getStringList('softdepend');
    }

    public function getExtra(string $key, mixed $default = null): mixed
    {
        return $this->extra[$key] ?? $default;
    }

    private function getString(string $key, string $default = ''): string
    {
        $value = $this->extra[$key] ?? $default;
        return is_scalar($value) ? trim((string) $value) : $default;
    }

    /**
     * @return list<string>
     */
    private function getStringList(string $key): array
    {
        $value = $this->extra[$key] ?? [];
        if (is_string($value)) {
            $value = [$value];
        }
        if (!is_array($value)) {
            return [];
        }

        $values = [];
        foreach ($value as $item) {
            if (is_string($item) && trim($item) !== '') {
                $values[] = trim($item);
            }
        }
        return array_values(array_unique($values));
    }
}
