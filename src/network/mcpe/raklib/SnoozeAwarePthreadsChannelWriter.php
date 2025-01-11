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

namespace watermossmc\network\mcpe\raklib;

use pmmp\thread\ThreadSafeArray;
use watermossmc\network\raklib\server\ipc\InterThreadChannelWriter;
use watermossmc\snooze\SleeperNotifier;

final class SnoozeAwarePthreadsChannelWriter implements InterThreadChannelWriter
{
	/**
	 * @phpstan-param ThreadSafeArray<int, string> $buffer
	 */
	public function __construct(
		private ThreadSafeArray $buffer,
		private SleeperNotifier $notifier
	) {
	}

	public function write(string $str) : void
	{
		$this->buffer[] = $str;
		$this->notifier->wakeupSleeper();
	}
}
