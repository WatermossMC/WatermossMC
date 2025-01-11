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

namespace watermossmc\utils;

use watermossmc\thread\Thread;

use function hrtime;
use function intdiv;

class ServerKiller extends Thread
{
	private bool $stopped = false;

	public function __construct(
		public int $time = 15
	) {
	}

	protected function onRun() : void
	{
		$start = hrtime(true);
		$remaining = $this->time * 1_000_000;
		$this->synchronized(function () use (&$remaining, $start) : void {
			while (!$this->stopped && $remaining > 0) {
				$this->wait($remaining);
				$remaining -= intdiv(hrtime(true) - $start, 1000);
			}
		});
		if ($remaining <= 0) {
			echo "\nTook too long to stop, server was killed forcefully!\n";
			@Process::kill(Process::pid());
		}
	}

	public function quit() : void
	{
		$this->synchronized(function () : void {
			$this->stopped = true;
			$this->notify();
		});
		parent::quit();
	}

	public function getThreadName() : string
	{
		return "Server Killer";
	}
}
