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

/**
 * This trait provides destructor callback functionality to objects which use it. This enables a weakmap-like system
 * to function without actually having weak maps.
 * TODO: remove this in PHP 8
 */
trait DestructorCallbackTrait
{
	/**
	 * @var ObjectSet
	 * @phpstan-var ObjectSet<\Closure() : void>|null
	 */
	private $destructorCallbacks = null;

	/** @phpstan-return ObjectSet<\Closure() : void> */
	public function getDestructorCallbacks() : ObjectSet
	{
		return $this->destructorCallbacks === null ? ($this->destructorCallbacks = new ObjectSet()) : $this->destructorCallbacks;
	}

	public function __destruct()
	{
		if ($this->destructorCallbacks !== null) {
			foreach ($this->destructorCallbacks as $callback) {
				$callback();
			}
		}
	}
}
