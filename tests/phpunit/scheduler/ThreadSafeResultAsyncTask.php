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

use pmmp\thread\ThreadSafeArray;
use watermossmc\promise\PromiseResolver;

class ThreadSafeResultAsyncTask extends AsyncTask
{
	private const TLS_KEY_PROMISE = "promise";

	/**
	 * @phpstan-param PromiseResolver<ThreadSafeArray<array-key, mixed>> $promise
	 */
	public function __construct(
		PromiseResolver $promise
	) {
		$this->storeLocal(self::TLS_KEY_PROMISE, $promise);
	}

	public function onRun() : void
	{
		//this only works in pthreads 5.1+ and pmmpthread
		//in prior versions the ThreadSafe would be destroyed before onCompletion is called
		$result = new ThreadSafeArray();
		$result[] = "foo";
		$this->setResult($result);
	}

	public function onCompletion() : void
	{
		/** @var PromiseResolver<ThreadSafeArray<array-key, mixed>> $promise */
		$promise = $this->fetchLocal(self::TLS_KEY_PROMISE);
		/** @var ThreadSafeArray<array-key, mixed> $result */
		$result = $this->getResult();
		$promise->resolve($result);
	}
}
