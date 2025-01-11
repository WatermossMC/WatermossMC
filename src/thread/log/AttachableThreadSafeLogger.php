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

namespace watermossmc\thread\log;

use pmmp\thread\ThreadSafeArray;

abstract class AttachableThreadSafeLogger extends ThreadSafeLogger
{
	/**
	 * @var ThreadSafeArray|ThreadSafeLoggerAttachment[]
	 * @phpstan-var ThreadSafeArray<int, ThreadSafeLoggerAttachment>
	 */
	protected ThreadSafeArray $attachments;

	public function __construct()
	{
		$this->attachments = new ThreadSafeArray();
	}

	public function addAttachment(ThreadSafeLoggerAttachment $attachment) : void
	{
		$this->attachments[] = $attachment;
	}

	public function removeAttachment(ThreadSafeLoggerAttachment $attachment) : void
	{
		foreach ($this->attachments as $i => $a) {
			if ($attachment === $a) {
				unset($this->attachments[$i]);
			}
		}
	}

	public function removeAttachments() : void
	{
		foreach ($this->attachments as $i => $a) {
			unset($this->attachments[$i]);
		}
	}

	/**
	 * @return ThreadSafeLoggerAttachment[]
	 */
	public function getAttachments() : array
	{
		return (array) $this->attachments;
	}
}
