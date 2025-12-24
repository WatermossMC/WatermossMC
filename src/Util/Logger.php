<?php

declare(strict_types=1);

namespace WatermossMC\Util;

final class Logger
{
    private static function log(string $level, string $msg): void
    {
        $time = date("H:i:s");
        echo "[$time] [$level]: $msg\n";
    }

    public static function info(string $msg): void
    {
        self::log("INFO", $msg);
    }

    public static function debug(string $msg): void
    {
        self::log("DEBUG", $msg);
    }

    public static function warning(string $msg): void
    {
        self::log("WARN", $msg);
    }

    public static function error(string $msg): void
    {
        self::log("ERROR", $msg);
    }
}
