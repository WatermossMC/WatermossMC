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

namespace watermossmc\network\mcpe\protocol\types\command;

class CommandData
{
	/**
	 * @param CommandOverload[]       $overloads
	 * @param ChainedSubCommandData[] $chainedSubCommandData
	 */
	public function __construct(
		public string $name,
		public string $description,
		public int $flags,
		public int $permission,
		public ?CommandEnum $aliases,
		public array $overloads,
		public array $chainedSubCommandData
	) {
		(function (CommandOverload ...$overloads) : void {})(...$overloads);
		(function (ChainedSubCommandData ...$chainedSubCommandData) : void {})(...$chainedSubCommandData);
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getDescription() : string
	{
		return $this->description;
	}

	public function getFlags() : int
	{
		return $this->flags;
	}

	public function getPermission() : int
	{
		return $this->permission;
	}

	public function getAliases() : ?CommandEnum
	{
		return $this->aliases;
	}

	/**
	 * @return CommandOverload[]
	 */
	public function getOverloads() : array
	{
		return $this->overloads;
	}

	/**
	 * @return ChainedSubCommandData[]
	 */
	public function getChainedSubCommandData() : array
	{
		return $this->chainedSubCommandData;
	}
}
