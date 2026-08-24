<?php

declare(strict_types=1);

namespace watermossmc\util;

use Throwable;

/** Central server logger for console and optional file output. */
final class Logger
{
    private const RESET = "\033[0m";
    private const COLORS = ['DEBUG' => "\033[0;90m", 'INFO' => "\033[1;36m", 'SUCCESS' => "\033[1;32m", 'WARN' => "\033[1;33m", 'ERROR' => "\033[1;31m"];
    private const LEVELS = ['DEBUG' => 100, 'INFO' => 200, 'SUCCESS' => 200, 'WARN' => 300, 'ERROR' => 400];

    private static int $minimumLevel = self::LEVELS['INFO'];
    private static bool $colorsEnabled = false;
    private static ?string $filePath = null;

    /** Configures logging from server.properties and the DEBUG environment variable. */
    public static function init(?bool $forceDebug = null): void
    {
        $debugEnabled = $forceDebug ?? self::readDebugSetting();
        self::setMinimumLevel(Config::getString('log-level', $debugEnabled ? 'debug' : 'info'));
        self::$colorsEnabled = self::isInteractiveTerminal() && Config::getBool('log-color', true);
        self::setLogFile(Config::getString('log-file', 'logs/server.log'));
    }

    public static function setMinimumLevel(string $level): void
    {
        $level = strtoupper(trim($level));
        if (!isset(self::LEVELS[$level])) {
            throw new \InvalidArgumentException('Unknown log level: ' . $level);
        }
        self::$minimumLevel = self::LEVELS[$level];
    }

    public static function getMinimumLevel(): string
    {
        foreach (self::LEVELS as $name => $value) {
            if ($value === self::$minimumLevel && $name !== 'SUCCESS') {
                return strtolower($name);
            }
        }
        return 'info';
    }

    public static function setColorsEnabled(bool $enabled): void
    {
        self::$colorsEnabled = $enabled;
    }

    /**
     * Sets the destination file. Pass null to disable file logging.
     *
     * @throws \RuntimeException If the file or its directory cannot be opened.
     */
    public static function setLogFile(?string $path): void
    {
        if ($path === null || trim($path) === '') {
            self::$filePath = null;
            return;
        }
        $path = trim($path);
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0o777, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create log directory: ' . $directory);
        }
        if (@file_put_contents($path, '', FILE_APPEND | LOCK_EX) === false) {
            throw new \RuntimeException('Unable to write log file: ' . $path);
        }
        self::$filePath = $path;
    }

    /** @param array<string, scalar|null> $context */
    public static function debug(string $message, array $context = []): void { self::log('DEBUG', $message, $context); }

    /** @param array<string, scalar|null> $context */
    public static function info(string $message, array $context = []): void { self::log('INFO', $message, $context); }

    /** @param array<string, scalar|null> $context */
    public static function success(string $message, array $context = []): void { self::log('SUCCESS', $message, $context); }

    /** @param array<string, scalar|null> $context */
    public static function warning(string $message, array $context = []): void { self::log('WARN', $message, $context); }

    /** @param array<string, scalar|null> $context */
    public static function error(string $message, array $context = []): void { self::log('ERROR', $message, $context); }

    /** @param array<string, scalar|null> $context */
    public static function exception(Throwable $exception, array $context = []): void
    {
        self::error($exception::class . ': ' . $exception->getMessage(), [...$context, 'file' => $exception->getFile(), 'line' => $exception->getLine()]);
        self::debug($exception->getTraceAsString());
    }

    /** @param array<string, scalar|null> $context */
    public static function log(string $level, string $message, array $context = []): void
    {
        $level = strtoupper($level);
        if (!isset(self::LEVELS[$level])) {
            throw new \InvalidArgumentException('Unknown log level: ' . $level);
        }
        if (self::LEVELS[$level] < self::$minimumLevel) {
            return;
        }
        $line = sprintf('[%s] [%-7s] %s%s', (new \DateTimeImmutable())->format('Y-m-d H:i:s.v'), $level, $message, self::formatContext($context));
        echo self::$colorsEnabled ? self::COLORS[$level] . $line . self::RESET . PHP_EOL : $line . PHP_EOL;
        if (self::$filePath !== null && @file_put_contents(self::$filePath, $line . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            self::$filePath = null;
            echo '[Logger] File logging disabled because writing to the configured log file failed.' . PHP_EOL;
        }
    }

    private static function readDebugSetting(): bool
    {
        if (Config::has('debug')) {
            return Config::getBool('debug', false);
        }
        $debug = getenv('DEBUG');
        return $debug !== false && filter_var($debug, FILTER_VALIDATE_BOOL);
    }

    private static function isInteractiveTerminal(): bool
    {
        return function_exists('stream_isatty') && stream_isatty(STDOUT);
    }

    /** @param array<string, scalar|null> $context */
    private static function formatContext(array $context): string
    {
        if ($context === []) {
            return '';
        }
        $pairs = [];
        foreach ($context as $key => $value) {
            $rendered = match (true) {
                $value === null => 'null',
                is_bool($value) => $value ? 'true' : 'false',
                is_string($value) && preg_match('/[\s=]/', $value) === 1 => '"' . addcslashes($value, "\\\"") . '"',
                default => (string) $value,
            };
            $pairs[] = $key . '=' . $rendered;
        }
        return ' {' . implode(', ', $pairs) . '}';
    }
}
