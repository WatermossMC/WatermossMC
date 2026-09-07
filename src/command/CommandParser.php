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

/** @internal Shared command-line parser used by CommandMap. */
final class CommandParser
{
    /**
     * Splits a command line while preserving quoted arguments and escaped quotes.
     *
     * @return list<string>
     */
    public static function parse(string $commandLine): array
    {
        $tokens = [];
        $current = '';
        $quote = null;
        $escaping = false;

        foreach (str_split(trim($commandLine)) as $character) {
            if ($escaping) {
                $current .= $character;
                $escaping = false;
                continue;
            }
            if ($character === '\\') {
                $escaping = true;
                continue;
            }
            if (($character === '"' || $character === "'") && ($quote === null || $quote === $character)) {
                $quote = $quote === null ? $character : null;
                continue;
            }
            if (ctype_space($character) && $quote === null) {
                if ($current !== '') {
                    $tokens[] = $current;
                    $current = '';
                }
                continue;
            }
            $current .= $character;
        }
        if ($escaping) {
            $current .= '\\';
        }
        if ($current !== '') {
            $tokens[] = $current;
        }
        return $tokens;
    }
}
