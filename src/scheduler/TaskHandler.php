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

namespace watermossmc\scheduler;

use watermossmc\timings\Timings;
use watermossmc\timings\TimingsHandler;

/**
 * @template TTask of Task
 */
class TaskHandler
{
	protected int $nextRun;

	protected bool $cancelled = false;

	private TimingsHandler $timings;

	private string $taskName;
	private string $ownerName;

	/**
	 * @phpstan-param TTask $task
	 */
	public function __construct(
		protected Task $task,
		protected int $delay = -1,
		protected int $period = -1,
		?string $ownerName = null
	) {
		if ($task->getHandler() !== null) {
			throw new \InvalidArgumentException("Cannot assign multiple handlers to the same task");
		}
		$this->taskName = $task->getName();
		$this->ownerName = $ownerName ?? "Unknown";
		$this->timings = Timings::getScheduledTaskTimings($this, $period);
		$this->task->setHandler($this);
	}

	public function isCancelled() : bool
	{
		return $this->cancelled;
	}

	public function getNextRun() : int
	{
		return $this->nextRun;
	}

	/**
	 * @internal
	 */
	public function setNextRun(int $ticks) : void
	{
		$this->nextRun = $ticks;
	}

	/**
	 * @phpstan-return TTask
	 */
	public function getTask() : Task
	{
		return $this->task;
	}

	public function getDelay() : int
	{
		return $this->delay;
	}

	public function isDelayed() : bool
	{
		return $this->delay > 0;
	}

	public function isRepeating() : bool
	{
		return $this->period > 0;
	}

	public function getPeriod() : int
	{
		return $this->period;
	}

	public function cancel() : void
	{
		try {
			if (!$this->isCancelled()) {
				$this->task->onCancel();
			}
		} finally {
			$this->remove();
		}
	}

	/**
	 * @internal
	 */
	public function remove() : void
	{
		$this->cancelled = true;
		$this->task->setHandler(null);
	}

	/**
	 * @internal
	 */
	public function run() : void
	{
		$this->timings->startTiming();
		try {
			$this->task->onRun();
		} catch (CancelTaskException $e) {
			$this->cancel();
		} finally {
			$this->timings->stopTiming();
		}
	}

	public function getTaskName() : string
	{
		return $this->taskName;
	}

	public function getOwnerName() : string
	{
		return $this->ownerName;
	}
}
