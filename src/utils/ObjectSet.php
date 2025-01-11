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

use function array_key_exists;
use function spl_object_id;

/**
 * @phpstan-template T of object
 * @phpstan-implements \IteratorAggregate<int, T>
 */
final class ObjectSet implements \IteratorAggregate
{
	/**
	 * @var object[]
	 * @phpstan-var array<int, T>
	 */
	private array $objects = [];

	/** @phpstan-param T ...$objects */
	public function add(object ...$objects) : void
	{
		foreach ($objects as $object) {
			$this->objects[spl_object_id($object)] = $object;
		}
	}

	/** @phpstan-param T ...$objects */
	public function remove(object ...$objects) : void
	{
		foreach ($objects as $object) {
			unset($this->objects[spl_object_id($object)]);
		}
	}

	public function clear() : void
	{
		$this->objects = [];
	}

	public function contains(object $object) : bool
	{
		return array_key_exists(spl_object_id($object), $this->objects);
	}

	/** @phpstan-return \ArrayIterator<int, T> */
	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->objects);
	}

	/**
	 * @return object[]
	 * @phpstan-return array<int, T>
	 */
	public function toArray() : array
	{
		return $this->objects;
	}
}
