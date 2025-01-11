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

namespace watermossmc\event;

use watermossmc\timings\Timings;

use function count;
use function get_class;

abstract class Event
{
	private const MAX_EVENT_CALL_DEPTH = 50;

	private static int $eventCallDepth = 1;

	protected ?string $eventName = null;

	final public function getEventName() : string
	{
		return $this->eventName ?? get_class($this);
	}

	/**
	 * Calls event handlers registered for this event.
	 *
	 * @throws \RuntimeException if event call recursion reaches the max depth limit
	 */
	public function call() : void
	{
		if (self::$eventCallDepth >= self::MAX_EVENT_CALL_DEPTH) {
			//this exception will be caught by the parent event call if all else fails
			throw new \RuntimeException("Recursive event call detected (reached max depth of " . self::MAX_EVENT_CALL_DEPTH . " calls)");
		}

		$timings = Timings::getEventTimings($this);
		$timings->startTiming();

		$handlers = HandlerListManager::global()->getHandlersFor(static::class);

		++self::$eventCallDepth;
		try {
			foreach ($handlers as $registration) {
				$registration->callEvent($this);
			}
		} finally {
			--self::$eventCallDepth;
			$timings->stopTiming();
		}
	}

	/**
	 * Returns whether the current class context has any registered global handlers.
	 * This can be used in hot code paths to avoid unnecessary event object creation.
	 *
	 * Usage: SomeEventClass::hasHandlers()
	 */
	public static function hasHandlers() : bool
	{
		return count(HandlerListManager::global()->getHandlersFor(static::class)) > 0;
	}
}
