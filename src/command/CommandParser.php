<?php

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
