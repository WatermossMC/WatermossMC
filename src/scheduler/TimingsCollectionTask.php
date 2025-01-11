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

use watermossmc\promise\PromiseResolver;
use watermossmc\timings\TimingsHandler;

/**
 * @phpstan-type Resolver PromiseResolver<list<string>>
 */
final class TimingsCollectionTask extends AsyncTask
{
	private const TLS_KEY_RESOLVER = "resolver";

	/**
	 * @phpstan-param PromiseResolver<list<string>> $promiseResolver
	 */
	public function __construct(PromiseResolver $promiseResolver)
	{
		$this->storeLocal(self::TLS_KEY_RESOLVER, $promiseResolver);
	}

	public function onRun() : void
	{
		$this->setResult(TimingsHandler::printCurrentThreadRecords());
	}

	public function onCompletion() : void
	{
		/**
		 * @var string[] $result
		 * @phpstan-var list<string> $result
		 */
		$result = $this->getResult();
		/**
		 * @var PromiseResolver $promiseResolver
		 * @phpstan-var PromiseResolver<list<string>> $promiseResolver
		 */
		$promiseResolver = $this->fetchLocal(self::TLS_KEY_RESOLVER);

		$promiseResolver->resolve($result);
	}
}
