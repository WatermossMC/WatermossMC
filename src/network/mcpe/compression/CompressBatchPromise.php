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

namespace watermossmc\network\mcpe\compression;

use function array_push;

class CompressBatchPromise
{
	/**
	 * @var \Closure[]
	 * @phpstan-var (\Closure(self) : void)[]
	 */
	private array $callbacks = [];

	private ?string $result = null;

	private bool $cancelled = false;

	/**
	 * @phpstan-param \Closure(self) : void ...$callbacks
	 */
	public function onResolve(\Closure ...$callbacks) : void
	{
		$this->checkCancelled();
		if ($this->result !== null) {
			foreach ($callbacks as $callback) {
				$callback($this);
			}
		} else {
			array_push($this->callbacks, ...$callbacks);
		}
	}

	public function resolve(string $result) : void
	{
		if (!$this->cancelled) {
			if ($this->result !== null) {
				throw new \LogicException("Cannot resolve promise more than once");
			}
			$this->result = $result;
			foreach ($this->callbacks as $callback) {
				$callback($this);
			}
			$this->callbacks = [];
		}
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return (\Closure(self) : void)[]
	 */
	public function getResolveCallbacks() : array
	{
		return $this->callbacks;
	}

	public function getResult() : string
	{
		$this->checkCancelled();
		if ($this->result === null) {
			throw new \LogicException("Promise has not yet been resolved");
		}
		return $this->result;
	}

	public function hasResult() : bool
	{
		return $this->result !== null;
	}

	public function isCancelled() : bool
	{
		return $this->cancelled;
	}

	public function cancel() : void
	{
		if ($this->hasResult()) {
			throw new \LogicException("Cannot cancel a resolved promise");
		}
		$this->cancelled = true;
	}

	private function checkCancelled() : void
	{
		if ($this->cancelled) {
			throw new \InvalidArgumentException("Promise has been cancelled");
		}
	}
}
