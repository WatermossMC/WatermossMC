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

namespace watermossmc\player;

use watermossmc\Server;
use watermossmc\util\Permission;

final class OperatorManager
{
    /** @var array<string, int> */
    private static array $ops = [];

    public static function load(Server $server): void
    {
        $opFile = $server->getRootPath() . '/ops.txt';
        if (!is_file($opFile)) {
            return;
        }

        $lines = file($opFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $parts = explode(',', trim($line));
            if (count($parts) >= 1) {
                $name = trim($parts[0]);
                // Default to OPERATOR if no role is specified
                $role = isset($parts[1]) ? (int) trim($parts[1]) : Permission::ROLE_OPERATOR;
                self::$ops[$name] = $role;
            }
        }
    }

    public static function getPermissionLevel(string $username): int
    {
        return self::$ops[$username] ?? Permission::ROLE_MEMBER;
    }

    public static function setOp(string $username, int $role): void
    {
        self::$ops[$username] = $role;
    }

    public static function removeOp(string $username): bool
    {
        foreach (self::$ops as $name => $role) {
            if (strcasecmp($name, $username) === 0) {
                unset(self::$ops[$name]);
                return true;
            }
        }
        return false;
    }

    public static function save(Server $server): void
    {
        $opFile = $server->getRootPath() . '/ops.txt';
        $content = '';
        foreach (self::$ops as $name => $level) {
            $content .= "$name,$level
";
        }
        file_put_contents($opFile, $content);
    }
}
