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

use watermossmc\event\Listener;
use watermossmc\event\server\CommandEvent;
use watermossmc\plugin\PluginBase;
use watermossmc\scheduler\CancelTaskException;
use watermossmc\scheduler\ClosureTask;

use function array_shift;

class Main extends PluginBase implements Listener
{
	/** @var Test[] */
	protected $waitingTests = [];
	/** @var Test|null */
	protected $currentTest = null;
	/** @var Test[] */
	protected $completedTests = [];
	/** @var int */
	protected $currentTestNumber = 0;

	public function onEnable() : void
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () : void {
			if ($this->currentTest === null) {
				if (!$this->startNextTest()) {
					$this->onAllTestsCompleted();
					throw new CancelTaskException();
				}
			} elseif ($this->currentTest->isFinished() || $this->currentTest->isTimedOut()) {
				$this->onTestCompleted($this->currentTest);
			} else {
				$this->currentTest->tick();
			}
		}), 10);

		$this->waitingTests = [
			//Add test objects here
		];
	}

	public function onServerCommand(CommandEvent $event) : void
	{
		//The CI will send this command as a failsafe to prevent the build from hanging if the tester plugin failed to
		//run. However, if the plugin loaded successfully we don't want to allow this to stop the server as there may
		//be asynchronous tests running. Instead we cancel this and stop the server of our own accord once all tests
		//have completed.
		if ($event->getCommand() === "stop") {
			$event->cancel();
		}
	}

	private function startNextTest() : bool
	{
		$this->currentTest = array_shift($this->waitingTests);
		if ($this->currentTest !== null) {
			$this->getLogger()->notice("Running test #" . (++$this->currentTestNumber) . " (" . $this->currentTest->getName() . ")");
			$this->currentTest->start();
			return true;
		}

		return false;
	}

	private function onTestCompleted(Test $test) : void
	{
		$message = "Finished test #" . $this->currentTestNumber . " (" . $test->getName() . "): ";
		switch ($test->getResult()) {
			case Test::RESULT_OK:
				$message .= "PASS";
				break;
			case Test::RESULT_FAILED:
				$message .= "FAIL";
				break;
			case Test::RESULT_ERROR:
				$message .= "ERROR";
				break;
			case Test::RESULT_WAITING:
				$message .= "TIMEOUT";
				break;
			default:
				$message .= "UNKNOWN";
				break;
		}

		$this->getLogger()->notice($message);

		$this->completedTests[$this->currentTestNumber] = $test;
		$this->currentTest = null;
	}

	private function onAllTestsCompleted() : void
	{
		$this->getLogger()->notice("All tests finished, stopping the server");
		$this->getServer()->shutdown();
	}
}
