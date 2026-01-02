<?php

declare(strict_types=1);

namespace WatermossMC\Util;

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
    /** private static bool $debugEnabled = true; */
    private static function log(string $level, string $msg, string $color): void
    {
        $time = date('H:i:s');

        $prefix = sprintf(
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
        /** if (!self::$debugEnabled) {
            return;
        } */

        self::log('DEBUG', $msg, self::GRAY);
    }

    /**
    public static function enableDebug(bool $state = true): void
    {
        self::$debugEnabled = $state;
    }
    */
}
