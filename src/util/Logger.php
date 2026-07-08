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

final class Logger
{
    /** ANSI Colors */
    private const RESET = "\033[0m";

    private const GRAY = "\033[0;90m";
    private const RED = "\033[1;31m";
    private const GREEN = "\033[1;32m";
    private const YELLOW = "\033[1;33m";
    private const CYAN = "\033[1;36m";

    /** Config */
    private static bool $debugEnabled = false;

    public static function init(?bool $forceDebug = null): void
    {
        if ($forceDebug !== null) {
            self::$debugEnabled = $forceDebug;
            return;
        }

        if (Config::has('debug')) {
            self::$debugEnabled = Config::getBool('debug', false);
            return;
        }

        $debugEnv = getenv('DEBUG');
        self::$debugEnabled = $debugEnv === false || $debugEnv === '' || $debugEnv === '1' || $debugEnv === 'true';
    }

    private static function log(string $level, string $msg, string $color): void
    {
        $time = date('H:i:s');

        $prefix = \sprintf(
            '[%s] [%s]',
            $time,
            str_pad($level, 7, ' ', \STR_PAD_RIGHT)
        );

        echo self::RESET . $prefix . ' ' . $color . $msg . self::RESET . \PHP_EOL;
    }

    public static function info(string $msg): void
    {
        self::log('INFO', $msg, self::CYAN);
    }

    public static function success(string $msg): void
    {
        self::log('SUCCESS', $msg, self::GREEN);
    }

    public static function warning(string $msg): void
    {
        self::log('WARN', $msg, self::YELLOW);
    }

    public static function error(string $msg): void
    {
        self::log('ERROR', $msg, self::RED);
    }

    public static function debug(string $msg): void
    {
        if (!self::$debugEnabled) {
            return;
        }

        self::log('DEBUG', $msg, self::GRAY);
    }

    /**
    public static function enableDebug(bool $state = true): void
    {
        self::$debugEnabled = $state;
    }
    */
}
