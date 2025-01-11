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

use pmmp\thread\ThreadSafe;
use pmmp\thread\ThreadSafeArray;

/**
 * Represents an entry in a {@link SleeperHandler}. This is used to unregister the notifier when it is no longer
 * needed. It is also used to create the {@link SleeperNotifier} for the thread that needs to do wakeups.
 *
 * Since notifiers themselves are not shared between threads, they don't need to be thread-safe. We only need to pass
 * the information needed to construct the notifier to the destination thread.
 * This approach maximizes performance by avoiding unnecessary overhead of extra ThreadSafe objects.
 *
 * Pass this object into the thread that needs to do wakeups, and then create a notifier using
 * {@link SleeperHandlerEntry::createNotifier()}.
 *
 * Obtain this by calling {@link SleeperHandler::addNotifier()}.
 */
final class SleeperHandlerEntry extends ThreadSafe
{
	/**
	 * @internal
	 * Do not construct this object directly. Use {@link SleeperHandler::addNotifier()} instead.
	 *
	 * @phpstan-param ThreadSafeArray<int, int> $sharedObject
	 */
	public function __construct(
		private readonly ThreadSafeArray $sharedObject,
		private readonly int $id
	) {
	}

	final public function getNotifierId() : int
	{
		return $this->id;
	}

	/**
	 * Constructs a notifier for this entry. Call this inside the thread that needs to do wakeups to get a notifier
	 * instance.
	 */
	public function createNotifier() : SleeperNotifier
	{
		return new SleeperNotifier($this->sharedObject, $this->id);
	}
}
