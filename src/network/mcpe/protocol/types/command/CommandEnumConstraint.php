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

class CommandEnumConstraint
{
	public const REQUIRES_CHEATS_ENABLED = 1 << 0;
	public const REQUIRES_ELEVATED_PERMISSIONS = 1 << 1;
	public const REQUIRES_HOST_PERMISSIONS = 1 << 2;
	public const REQUIRES_ALLOW_ALIASES = 1 << 3;

	/**
	 * @param int[] $constraints
	 */
	public function __construct(
		private CommandEnum $enum,
		private int $valueOffset,
		private array $constraints
	) {
		(static function (int ...$_) : void {})(...$constraints);
		if (!isset($enum->getValues()[$valueOffset])) {
			throw new \InvalidArgumentException("Invalid enum value offset $valueOffset");
		}
	}

	public function getEnum() : CommandEnum
	{
		return $this->enum;
	}

	public function getValueOffset() : int
	{
		return $this->valueOffset;
	}

	public function getAffectedValue() : string
	{
		return $this->enum->getValues()[$this->valueOffset];
	}

	/**
	 * @return int[]
	 */
	public function getConstraints() : array
	{
		return $this->constraints;
	}
}
