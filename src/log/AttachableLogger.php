<?php

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

/**
 * @phpstan-type LoggerAttachment \Closure(mixed $level, string $message) : void
 */
interface AttachableLogger extends Logger
{
	/**
	 * @phpstan-param LoggerAttachment $attachment
	 *
	 * @return void
	 */
	public function addAttachment(Closure $attachment);

	/**
	 * @phpstan-param LoggerAttachment $attachment
	 *
	 * @return void
	 */
	public function removeAttachment(Closure $attachment);

	/**
	 * @return void
	 */
	public function removeAttachments();

	/**
	 * @return Closure[]
	 * @phpstan-return LoggerAttachment[]
	 */
	public function getAttachments();
}
