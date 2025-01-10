<?php

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

interface Logger
{
	/**
	 * System is unusable
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function emergency($message);

	/**
	 * Action must be taken immediately
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function alert($message);

	/**
	 * Critical conditions
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function critical($message);

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function error($message);

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function warning($message);

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function notice($message);

	/**
	 * Interesting events.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function info($message);

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function debug($message);

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 *
	 * @return void
	 */
	public function log($level, $message);

	/**
	 * Logs a Throwable object
	 *
	 * @param array|null $trace
	 * @phpstan-param list<array<string, mixed>>|null $trace
	 *
	 * @return void
	 */
	public function logException(Throwable $e, $trace = null);
}
