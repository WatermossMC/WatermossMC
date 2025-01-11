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

namespace watermossmc\network\mcpe\protocol\types\entity;

trait IntegerishMetadataProperty
{
	public function __construct(
		private int $value
	) {
		if ($value < $this->min() || $value > $this->max()) {
			throw new \InvalidArgumentException("Value is out of range " . $this->min() . " - " . $this->max());
		}
	}

	abstract protected function min() : int;

	abstract protected function max() : int;

	public function getValue() : int
	{
		return $this->value;
	}

	public function equals(MetadataProperty $other) : bool
	{
		return $other instanceof self && $other->value === $this->value;
	}

	/**
	 * @param bool[] $flags
	 * @phpstan-param array<int, bool> $flags
	 */
	public static function buildFromFlags(array $flags) : self
	{
		$value = 0;
		foreach ($flags as $flag => $v) {
			if ($v) {
				$value |= 1 << $flag;
			}
		}
		return new self($value);
	}
}
