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

namespace watermossmc\errorhandler;

final class ErrorRecord
{
	/** @var int */
	private $severity;
	/** @var string */
	private $message;
	/** @var string */
	private $file;
	/** @var int */
	private $line;

	public function __construct(int $severity, string $message, string $file, int $line)
	{
		$this->severity = $severity;
		$this->message = $message;
		$this->file = $file;
		$this->line = $line;
	}

	public function getSeverity() : int
	{
		return $this->severity;
	}

	public function getMessage() : string
	{
		return $this->message;
	}

	public function getFile() : string
	{
		return $this->file;
	}

	public function getLine() : int
	{
		return $this->line;
	}
}
