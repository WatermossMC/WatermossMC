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

class PrefixedLogger extends SimpleLogger
{
	private Logger $delegate;
	private string $prefix;

	public function __construct(Logger $delegate, string $prefix)
	{
		$this->delegate = $delegate;
		$this->prefix = $prefix;
	}

	public function log($level, $message)
	{
		$this->delegate->log($level, "[$this->prefix] $message");
	}

	public function logException(Throwable $e, $trace = null)
	{
		$this->delegate->logException($e, $trace);
	}

	public function getPrefix() : string
	{
		return $this->prefix;
	}

	public function setPrefix(string $prefix) : void
	{
		$this->prefix = $prefix;
	}
}
