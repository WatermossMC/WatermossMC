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

use function count;
use function microtime;

/**
 * Manages a Threaded sleeper which can be waited on for notifications. Calls callbacks for attached notifiers when
 * notifications are received from the notifiers.
 */
class SleeperHandler
{
	/** @phpstan-var ThreadSafeArray<int, int> */
	private readonly ThreadSafeArray $sharedObject;

	/**
	 * @var \Closure[]
	 * @phpstan-var array<int, \Closure() : void>
	 */
	private array $handlers = [];

	private int $nextSleeperId = 0;

	public function __construct()
	{
		$this->sharedObject = new ThreadSafeArray();
	}

	/**
	 * @param \Closure $handler Called when the notifier wakes the server up, of the signature `function() : void`
	 * @phpstan-param \Closure() : void $handler
	 */
	public function addNotifier(\Closure $handler) : SleeperHandlerEntry
	{
		$id = $this->nextSleeperId++;
		$notifier = new SleeperHandlerEntry($this->sharedObject, $id);
		$this->handlers[$id] = $handler;
		return $notifier;
	}

	/**
	 * Removes a callback from the sleeper by its ID. Note that this does not prevent the notifier waking the sleeper
	 * up - it just stops the notifier getting actions processed from the main thread.
	 *
	 * @see SleeperHandlerEntry::getNotifierId()
	 */
	public function removeNotifier(int $notifierId) : void
	{
		unset($this->handlers[$notifierId]);
	}

	private function sleep(int $timeout) : void
	{
		$this->sharedObject->synchronized(function () use ($timeout) : void {
			if ($this->sharedObject->count() === 0) {
				$this->sharedObject->wait($timeout);
			}
		});
	}

	/**
	 * Sleeps until the given timestamp. Sleep may be interrupted by notifications, which will be processed before going
	 * back to sleep.
	 */
	public function sleepUntil(float $unixTime) : void
	{
		while (true) {
			$this->processNotifications();

			$sleepTime = (int) (($unixTime - microtime(true)) * 1000000);
			if ($sleepTime > 0) {
				$this->sleep($sleepTime);
			} else {
				break;
			}
		}
	}

	/**
	 * Blocks until notifications are received, then processes notifications. Will not sleep if notifications are
	 * already waiting.
	 */
	public function sleepUntilNotification() : void
	{
		$this->sleep(0);
		$this->processNotifications();
	}

	/**
	 * Processes any notifications from notifiers and calls handlers for received notifications.
	 */
	public function processNotifications() : void
	{
		while (true) {
			$notifierIds = $this->sharedObject->synchronized(function () : array {
				$notifierIds = [];
				foreach ($this->sharedObject as $notifierId => $_) {
					$notifierIds[$notifierId] = $notifierId;
					unset($this->sharedObject[$notifierId]);
				}
				return $notifierIds;
			});
			if (count($notifierIds) === 0) {
				break;
			}
			foreach ($notifierIds as $notifierId) {
				if (!isset($this->handlers[$notifierId])) {
					//a previously-removed notifier might still be sending notifications; ignore them
					continue;
				}
				$this->handlers[$notifierId]();
			}
		}
	}
}
