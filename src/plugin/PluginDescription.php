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
        $api = isset($data['api']) && \is_string($data['api']) ? $data['api'] : '1.0.0';
        return new self(trim($data['name']), trim($data['version']), trim($data['main']), trim($api), $data);
    }
}
