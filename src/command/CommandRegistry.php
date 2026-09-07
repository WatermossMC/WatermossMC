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

namespace watermossmc\command;

final class CommandRegistry
{
    /** @return list<class-string> */
    public static function getCommands(): array
    {
        $commands = [];
        $implPath = __DIR__ . '/impl';

        if (!is_dir($implPath)) {
            return [];
        }

        $files = glob($implPath . '/*.php');
        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            $className = 'watermossmc\\command\\impl\\' . basename($file, '.php');
            if (class_exists($className)) {
                $commands[] = $className;
            }
        }

        return $commands;
    }
}
