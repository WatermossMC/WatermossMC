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

namespace watermossmc\console;

use function hash;
use function strlen;
use function strrpos;
use function substr;

final class ConsoleReaderChildProcessUtils
{
	public const TOKEN_DELIMITER = ":";
	public const TOKEN_HASH_ALGO = "xxh3";

	private function __construct()
	{

	}

	/**
	 * Creates an IPC message to transmit a user's input command to the parent process.
	 *
	 * Unfortunately we can't currently provide IPC pipes other than stdout/stderr to subprocesses on Windows, so this
	 * adds a hash of the user input (with a counter as salt) to prevent unintended process output (like error messages)
	 * from being treated as user input.
	 */
	public static function createMessage(string $line, int &$counter) : string
	{
		$token = hash(self::TOKEN_HASH_ALGO, $line, options: ['seed' => $counter]);
		$counter++;
		return $line . self::TOKEN_DELIMITER . $token;
	}

	/**
	 * Extracts a command from an IPC message from the console reader subprocess.
	 * Returns the user's input command, or null if this isn't a user input.
	 */
	public static function parseMessage(string $message, int &$counter) : ?string
	{
		$delimiterPos = strrpos($message, self::TOKEN_DELIMITER);
		if ($delimiterPos !== false) {
			$left = substr($message, 0, $delimiterPos);
			$right = substr($message, $delimiterPos + strlen(self::TOKEN_DELIMITER));
			$expectedToken = hash(self::TOKEN_HASH_ALGO, $left, options: ['seed' => $counter]);

			if ($expectedToken === $right) {
				$counter++;
				return $left;
			}
		}

		return null;
	}
}
