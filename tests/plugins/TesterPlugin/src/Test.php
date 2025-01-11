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

namespace pmmp\TesterPlugin;

use function time;

abstract class Test
{
	public const RESULT_WAITING = -1;
	public const RESULT_OK = 0;
	public const RESULT_FAILED = 1;
	public const RESULT_ERROR = 2;

	/** @var Main */
	private $plugin;
	/** @var int */
	private $result = Test::RESULT_WAITING;
	/** @var int */
	private $startTime;
	/** @var int */
	private $timeout = 60; //seconds

	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
	}

	public function getPlugin() : Main
	{
		return $this->plugin;
	}

	final public function start() : void
	{
		$this->startTime = time();
		try {
			$this->run();
		} catch (TestFailedException $e) {
			$this->getPlugin()->getLogger()->error($e->getMessage());
			$this->setResult(Test::RESULT_FAILED);
		} catch (\Throwable $e) {
			$this->getPlugin()->getLogger()->logException($e);
			$this->setResult(Test::RESULT_ERROR);
		}
	}

	public function tick() : void
	{

	}

	abstract public function run() : void;

	public function isFinished() : bool
	{
		return $this->result !== Test::RESULT_WAITING;
	}

	public function isTimedOut() : bool
	{
		return !$this->isFinished() && time() - $this->timeout > $this->startTime;
	}

	protected function setTimeout(int $timeout) : void
	{
		$this->timeout = $timeout;
	}

	public function getResult() : int
	{
		return $this->result;
	}

	public function setResult(int $result) : void
	{
		$this->result = $result;
	}

	abstract public function getName() : string;

	abstract public function getDescription() : string;
}
