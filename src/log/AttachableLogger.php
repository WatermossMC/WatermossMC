<?php

declare(strict_types=1);

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
