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

namespace watermossmc\snooze;

use pmmp\thread\ThreadSafeArray;

/**
 * Used to wake up the sleeping thread from another thread.
 * Use {@link SleeperHandlerEntry::createNotifier()} inside the thread to create this.
 */
final class SleeperNotifier
{
	/**
	 * @internal
	 * Do not construct this object directly. Use {@link SleeperHandlerEntry::createNotifier()} instead.
	 *
	 * @phpstan-param ThreadSafeArray<int, int> $sharedObject
	 */
	public function __construct(
		private readonly ThreadSafeArray $sharedObject,
		private readonly int $notifierId
	) {
	}

	/**
	 * Call this method to wake up the sleeping thread.
	 */
	final public function wakeupSleeper() : void
	{
		$shared = $this->sharedObject;
		$sleeperId = $this->notifierId;
		$shared->synchronized(function () use ($shared, $sleeperId) : void {
			if (!isset($shared[$sleeperId])) {
				$shared[$sleeperId] = $sleeperId;
				$shared->notify();
			}
		});
	}
}
