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

namespace watermossmc\utils;

use function str_repeat;
use function strlen;
use function trim;

final class Git
{
	private function __construct()
	{
		//NOOP
	}

	/**
	 * Returns the git hash of the currently checked out head of the given repository, or null on failure.
	 *
	 * @param bool $dirty reference parameter, set to whether the repo has local changes
	 */
	public static function getRepositoryState(string $dir, bool &$dirty) : ?string
	{
		if (Process::execute("git -C \"$dir\" rev-parse HEAD", $out) === 0 && $out !== false && strlen($out = trim($out)) === 40) {
			if (Process::execute("git -C \"$dir\" diff --quiet") === 1 || Process::execute("git -C \"$dir\" diff --cached --quiet") === 1) { //Locally-modified
				$dirty = true;
			}
			return $out;
		}
		return null;
	}

	/**
	 * Infallible, returns a string representing git state, or a string of zeros on failure.
	 * If the repo is dirty, a "-dirty" suffix is added.
	 */
	public static function getRepositoryStatePretty(string $dir) : string
	{
		$dirty = false;
		$detectedHash = self::getRepositoryState($dir, $dirty);
		if ($detectedHash !== null) {
			return $detectedHash . ($dirty ? "-dirty" : "");
		}
		return str_repeat("00", 20);
	}
}
