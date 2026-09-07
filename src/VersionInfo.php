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

namespace watermossmc;

/** Single source of truth for WatermossMC release information. */
final class VersionInfo
{
    public const NAME = 'WatermossMC';
    public const VERSION = '0.1.0-unreleased';
    public const CHANNEL = 'development';
    public const MINECRAFT_VERSION = '1.26.40';
    public const REPOSITORY = 'https://github.com/watermossmc/WatermossMC';

    private function __construct() {}

    public static function getSoftwareVersion(): string
    {
        return self::VERSION;
    }

    /** Plugin API follows the WatermossMC version. */
    public static function getApiVersion(): string
    {
        return self::VERSION;
    }

    /**
     * @return array{name: string, version: string, api: string, minecraft: string, channel: string, repository: string}
     */
    public static function all(): array
    {
        return [
            'name' => self::NAME,
            'version' => self::VERSION,
            'api' => self::getApiVersion(),
            'minecraft' => self::MINECRAFT_VERSION,
            'channel' => self::CHANNEL,
            'repository' => self::REPOSITORY,
        ];
    }
}
