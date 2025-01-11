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

final class CommandOverload
{
	/**
	 * @param CommandParameter[] $parameters
	 */
	public function __construct(
		private bool $chaining,
		private array $parameters
	) {
		(function (CommandParameter ...$parameters) : void {})(...$parameters);
	}

	public function isChaining() : bool
	{
		return $this->chaining;
	}

	/**
	 * @return CommandParameter[]
	 */
	public function getParameters() : array
	{
		return $this->parameters;
	}
}
