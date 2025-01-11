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
