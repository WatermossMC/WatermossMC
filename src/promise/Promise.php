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

namespace watermossmc\promise;

use watermossmc\utils\Utils;

use function count;
use function spl_object_id;

/**
 * @phpstan-template TValue
 */
final class Promise
{
	/**
	 * @internal Do NOT call this directly; create a new Resolver and call Resolver->promise()
	 * @see PromiseResolver
	 * @phpstan-param PromiseSharedData<TValue> $shared
	 */
	public function __construct(private PromiseSharedData $shared)
	{
	}

	/**
	 * @phpstan-param \Closure(TValue) : void $onSuccess
	 * @phpstan-param \Closure() : void $onFailure
	 */
	public function onCompletion(\Closure $onSuccess, \Closure $onFailure) : void
	{
		$state = $this->shared->state;
		if ($state === true) {
			$onSuccess($this->shared->result);
		} elseif ($state === false) {
			$onFailure();
		} else {
			$this->shared->onSuccess[spl_object_id($onSuccess)] = $onSuccess;
			$this->shared->onFailure[spl_object_id($onFailure)] = $onFailure;
		}
	}

	public function isResolved() : bool
	{
		//TODO: perhaps this should return true when rejected? currently there's no way to tell if a promise was
		//rejected or just hasn't been resolved yet
		return $this->shared->state === true;
	}

	/**
	 * Returns a promise that will resolve only once all the Promises in
	 * `$promises` have resolved. The resolution value of the returned promise
	 * will be an array containing the resolution values of each Promises in
	 * `$promises` indexed by the respective Promises' array keys.
	 *
	 * @param Promise[] $promises
	 *
	 * @phpstan-template TPromiseValue
	 * @phpstan-template TKey of array-key
	 * @phpstan-param array<TKey, Promise<TPromiseValue>> $promises
	 *
	 * @phpstan-return Promise<array<TKey, TPromiseValue>>
	 */
	public static function all(array $promises) : Promise
	{
		/** @phpstan-var PromiseResolver<array<TKey, TPromiseValue>> $resolver */
		$resolver = new PromiseResolver();
		if (count($promises) === 0) {
			$resolver->resolve([]);
			return $resolver->getPromise();
		}
		$values = [];
		$toResolve = count($promises);
		$continue = true;

		foreach (Utils::promoteKeys($promises) as $key => $promise) {
			$promise->onCompletion(
				function (mixed $value) use ($resolver, $key, $toResolve, &$values) : void {
					$values[$key] = $value;

					if (count($values) === $toResolve) {
						$resolver->resolve($values);
					}
				},
				function () use ($resolver, &$continue) : void {
					if ($continue) {
						$continue = false;
						$resolver->reject();
					}
				}
			);

			if (!$continue) {
				break;
			}
		}

		return $resolver->getPromise();
	}
}
