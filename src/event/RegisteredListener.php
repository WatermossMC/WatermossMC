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

use watermossmc\plugin\Plugin;
use watermossmc\timings\TimingsHandler;

use function in_array;

class RegisteredListener
{
	public function __construct(
		private \Closure $handler,
		private int $priority,
		private Plugin $plugin,
		private bool $handleCancelled,
		private TimingsHandler $timings
	) {
		if (!in_array($priority, EventPriority::ALL, true)) {
			throw new \InvalidArgumentException("Invalid priority number $priority");
		}
	}

	public function getHandler() : \Closure
	{
		return $this->handler;
	}

	public function getPlugin() : Plugin
	{
		return $this->plugin;
	}

	public function getPriority() : int
	{
		return $this->priority;
	}

	public function callEvent(Event $event) : void
	{
		if ($event instanceof Cancellable && $event->isCancelled() && !$this->isHandlingCancelled()) {
			return;
		}
		$this->timings->startTiming();
		try {
			($this->handler)($event);
		} finally {
			$this->timings->stopTiming();
		}
	}

	public function isHandlingCancelled() : bool
	{
		return $this->handleCancelled;
	}
}
