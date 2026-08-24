<?php

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
