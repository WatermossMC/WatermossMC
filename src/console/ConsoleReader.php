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

use watermossmc\utils\Utils;

use function fclose;
use function fgets;
use function fopen;
use function is_resource;
use function stream_select;
use function trim;
use function usleep;

final class ConsoleReader
{
	/** @var resource */
	private $stdin;

	public function __construct()
	{
		$this->initStdin();
	}

	private function initStdin() : void
	{
		if (is_resource($this->stdin)) {
			fclose($this->stdin);
		}

		$this->stdin = Utils::assumeNotFalse(fopen("php://stdin", "r"), "Opening stdin should never fail");
	}

	/**
	 * Reads a line from the console and adds it to the buffer. This method may block the thread.
	 */
	public function readLine() : ?string
	{
		if (!is_resource($this->stdin)) {
			$this->initStdin();
		}

		$r = [$this->stdin];
		$w = $e = null;
		if (($count = stream_select($r, $w, $e, 0, 200000)) === 0) { //nothing changed in 200000 microseconds
			return null;
		} elseif ($count === false) { //stream error
			return null;
		}

		if (($raw = fgets($this->stdin)) === false) { //broken pipe or EOF
			usleep(200000); //prevent CPU waste if it's end of pipe
			return null; //loop back round
		}

		$line = trim($raw);

		return $line !== "" ? $line : null;
	}

	public function __destruct()
	{
		fclose($this->stdin);
	}
}
