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

namespace watermossmc\event\server;

use watermossmc\utils\Process;

/**
 * Called when the server is in a low-memory state as defined by the properties
 * Plugins should free caches or other non-essential data.
 */
class LowMemoryEvent extends ServerEvent
{
	public function __construct(
		private int $memory,
		private int $memoryLimit,
		private bool $isGlobal = false,
		private int $triggerCount = 0
	) {
	}

	/**
	 * Returns the memory usage at the time of the event call (in bytes)
	 */
	public function getMemory() : int
	{
		return $this->memory;
	}

	/**
	 * Returns the memory limit defined (in bytes)
	 */
	public function getMemoryLimit() : int
	{
		return $this->memoryLimit;
	}

	/**
	 * Returns the times this event has been called in the current low-memory state
	 */
	public function getTriggerCount() : int
	{
		return $this->triggerCount;
	}

	public function isGlobal() : bool
	{
		return $this->isGlobal;
	}

	/**
	 * Amount of memory already freed
	 */
	public function getMemoryFreed() : int
	{
		$usage = Process::getAdvancedMemoryUsage();
		return $this->getMemory() - ($this->isGlobal() ? $usage[1] : $usage[0]);
	}
}
