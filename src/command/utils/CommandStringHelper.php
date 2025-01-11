<?php

/*
 *
 * This file part of WatermossMC.
 *
 *  __        __    _                                    __  __  ____
 *  \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *   \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *    \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *     \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * @author WatermossMC Team
 * @license Apache 2.0
 */

declare(strict_types=1);

namespace watermossmc\command\utils;

use watermossmc\utils\AssumptionFailedError;

use function preg_last_error_msg;
use function preg_match_all;
use function preg_replace;

final class CommandStringHelper
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * Parses a command string into its component parts. Parts of the string which are inside unescaped quotes are
	 * considered as one argument.
	 *
	 * Examples:
	 * - `give "steve jobs" apple` -> ['give', 'steve jobs', 'apple']
	 * - `say "This is a \"string containing quotes\""` -> ['say', 'This is a "string containing quotes"']
	 *
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public static function parseQuoteAware(string $commandLine) : array
	{
		$args = [];
		preg_match_all('/"((?:\\\\.|[^\\\\"])*)"|(\S+)/u', $commandLine, $matches);
		foreach ($matches[0] as $k => $_) {
			for ($i = 1; $i <= 2; ++$i) {
				if ($matches[$i][$k] !== "") {
					/** @var string $match */ //phpstan can't understand preg_match and friends by itself :(
					$match = $matches[$i][$k];
					$args[(int) $k] = preg_replace('/\\\\([\\\\"])/u', '$1', $match) ?? throw new AssumptionFailedError(preg_last_error_msg());
					break;
				}
			}
		}

		return $args;
	}
}
