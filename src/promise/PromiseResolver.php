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

/**
 * @phpstan-template TValue
 */
final class PromiseResolver
{
	/** @phpstan-var PromiseSharedData<TValue> */
	private PromiseSharedData $shared;
	/** @phpstan-var Promise<TValue> */
	private Promise $promise;

	public function __construct()
	{
		$this->shared = new PromiseSharedData();
		$this->promise = new Promise($this->shared);
	}

	/**
	 * @phpstan-param TValue $value
	 */
	public function resolve(mixed $value) : void
	{
		if ($this->shared->state !== null) {
			throw new \LogicException("Promise has already been resolved/rejected");
		}
		$this->shared->state = true;
		$this->shared->result = $value;
		foreach ($this->shared->onSuccess as $c) {
			$c($value);
		}
		$this->shared->onSuccess = [];
		$this->shared->onFailure = [];
	}

	public function reject() : void
	{
		if ($this->shared->state !== null) {
			throw new \LogicException("Promise has already been resolved/rejected");
		}
		$this->shared->state = false;
		foreach ($this->shared->onFailure as $c) {
			$c();
		}
		$this->shared->onSuccess = [];
		$this->shared->onFailure = [];
	}

	/**
	 * @phpstan-return Promise<TValue>
	 */
	public function getPromise() : Promise
	{
		return $this->promise;
	}
}
