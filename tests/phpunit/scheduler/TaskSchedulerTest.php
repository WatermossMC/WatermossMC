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

use PHPUnit\Framework\TestCase;

class TaskSchedulerTest extends TestCase
{
	/** @var TaskScheduler */
	private $scheduler;

	public function setUp() : void
	{
		$this->scheduler = new TaskScheduler();
	}

	public function tearDown() : void
	{
		$this->scheduler->shutdown();
	}

	public function testCancel() : void
	{
		$task = $this->scheduler->scheduleTask(new ClosureTask(function () : void {
			throw new CancelTaskException();
		}));
		$this->scheduler->mainThreadHeartbeat(0);
		self::assertTrue($task->isCancelled(), "Task was not cancelled");
	}
}
