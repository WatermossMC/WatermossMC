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

namespace watermossmc;

use watermossmc\snooze\SleeperHandler;
use watermossmc\snooze\SleeperHandlerEntry;
use watermossmc\timings\TimingsHandler;
use watermossmc\utils\Utils;

use function hrtime;

/**
 * Custom Snooze sleeper handler which captures notification processing time.
 * @internal
 */
final class TimeTrackingSleeperHandler extends SleeperHandler
{
	private int $notificationProcessingTimeNs = 0;

	/**
	 * @var TimingsHandler[]
	 * @phpstan-var array<string, TimingsHandler>
	 */
	private static array $handlerTimings = [];

	public function __construct(
		private TimingsHandler $timings
	) {
		parent::__construct();
	}

	public function addNotifier(\Closure $handler) : SleeperHandlerEntry
	{
		$name = Utils::getNiceClosureName($handler);
		$timings = self::$handlerTimings[$name] ??= new TimingsHandler("Snooze Handler: " . $name, $this->timings);

		return parent::addNotifier(function () use ($timings, $handler) : void {
			$timings->startTiming();
			$handler();
			$timings->stopTiming();
		});
	}

	/**
	 * Returns the time in nanoseconds spent processing notifications since the last reset.
	 */
	public function getNotificationProcessingTime() : int
	{
		return $this->notificationProcessingTimeNs;
	}

	/**
	 * Resets the notification processing time tracker to zero.
	 */
	public function resetNotificationProcessingTime() : void
	{
		$this->notificationProcessingTimeNs = 0;
	}

	public function processNotifications() : void
	{
		$startTime = hrtime(true);
		$this->timings->startTiming();
		try {
			parent::processNotifications();
		} finally {
			$this->notificationProcessingTimeNs += hrtime(true) - $startTime;
			$this->timings->stopTiming();
		}
	}
}
