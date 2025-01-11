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

use pmmp\thread\Thread as NativeThread;
use Symfony\Component\Filesystem\Path;
use watermossmc\MemoryManager;

use function assert;

/**
 * Task used to dump memory from AsyncWorkers
 */
class DumpWorkerMemoryTask extends AsyncTask
{
	public function __construct(
		private string $outputFolder,
		private int $maxNesting,
		private int $maxStringSize
	) {
	}

	public function onRun() : void
	{
		$worker = NativeThread::getCurrentThread();
		assert($worker instanceof AsyncWorker);
		MemoryManager::dumpMemory(
			$worker,
			Path::join($this->outputFolder, "AsyncWorker#" . $worker->getAsyncWorkerId()),
			$this->maxNesting,
			$this->maxStringSize,
			new \PrefixedLogger($worker->getLogger(), "Memory Dump")
		);
	}
}
