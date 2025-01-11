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

class CommandEnum
{
	/**
	 * @param string[] $enumValues
	 * @param bool     $isSoft     Whether the enum is dynamic, i.e. can be updated during the game session
	 *
	 * @phpstan-param list<string> $enumValues
	 */
	public function __construct(
		private string $enumName,
		private array $enumValues,
		private bool $isSoft = false
	) {
	}

	public function getName() : string
	{
		return $this->enumName;
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getValues() : array
	{
		return $this->enumValues;
	}

	/**
	 * @return bool Whether the enum is dynamic, i.e. can be updated during the game session
	 */
	public function isSoft() : bool
	{
		return $this->isSoft;
	}
}
