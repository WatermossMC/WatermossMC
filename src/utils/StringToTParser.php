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

use function array_keys;
use function str_replace;
use function strtolower;
use function trim;

/**
 * Handles parsing any Minecraft thing from strings. This can be used, for example, to implement a user-friendly item
 * parser to be used by the /give command (and others).
 * Custom aliases may be registered.
 * Note that the aliases should be user-friendly, i.e. easily readable and writable.
 *
 * @phpstan-template T
 */
abstract class StringToTParser
{
	/**
	 * @var \Closure[]
	 * @phpstan-var array<string, \Closure(string $input) : T>
	 */
	private array $callbackMap = [];

	/** @phpstan-param \Closure(string $input) : T $callback */
	public function register(string $alias, \Closure $callback) : void
	{
		$key = $this->reprocess($alias);
		if (isset($this->callbackMap[$key])) {
			throw new \InvalidArgumentException("Alias \"$key\" is already registered");
		}
		$this->callbackMap[$key] = $callback;
	}

	/** @phpstan-param \Closure(string $input) : T $callback */
	public function override(string $alias, \Closure $callback) : void
	{
		$this->callbackMap[$this->reprocess($alias)] = $callback;
	}

	/**
	 * Registers a new alias for an existing known alias.
	 */
	public function registerAlias(string $existing, string $alias) : void
	{
		$existingKey = $this->reprocess($existing);
		if (!isset($this->callbackMap[$existingKey])) {
			throw new \InvalidArgumentException("Cannot register new alias for unknown existing alias \"$existing\"");
		}
		$newKey = $this->reprocess($alias);
		if (isset($this->callbackMap[$newKey])) {
			throw new \InvalidArgumentException("Alias \"$newKey\" is already registered");
		}
		$this->callbackMap[$newKey] = $this->callbackMap[$existingKey];
	}

	/**
	 * Tries to parse the specified string into a corresponding instance of T.
	 * @phpstan-return T|null
	 */
	public function parse(string $input)
	{
		$key = $this->reprocess($input);
		if (isset($this->callbackMap[$key])) {
			return ($this->callbackMap[$key])($input);
		}

		return null;
	}

	protected function reprocess(string $input) : string
	{
		return strtolower(str_replace([" ", "minecraft:"], ["_", ""], trim($input)));
	}

	/** @return string[]|int[] */
	public function getKnownAliases() : array
	{
		return array_keys($this->callbackMap);
	}
}
