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

use watermossmc\utils\Utils;

abstract class Task
{
	/** @phpstan-var TaskHandler<static>|null  */
	private ?TaskHandler $taskHandler = null;

	/**
	 * @phpstan-return TaskHandler<static>|null
	 */
	final public function getHandler() : ?TaskHandler
	{
		return $this->taskHandler;
	}

	public function getName() : string
	{
		return Utils::getNiceClassName($this);
	}

	/**
	 * @phpstan-param TaskHandler<static>|null $taskHandler
	 */
	final public function setHandler(?TaskHandler $taskHandler) : void
	{
		if ($this->taskHandler === null || $taskHandler === null) {
			$this->taskHandler = $taskHandler;
		}
	}

	/**
	 * Actions to execute when run
	 *
	 * @throws CancelTaskException
	 */
	abstract public function onRun() : void;

	/**
	 * Actions to execute if the Task is cancelled
	 */
	public function onCancel() : void
	{

	}
}
