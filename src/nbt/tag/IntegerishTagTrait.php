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

namespace watermossmc\nbt\tag;

use watermossmc\nbt\InvalidTagValueException;

use function func_num_args;

/**
 * This trait implements common parts of tags containing integer values.
 */
trait IntegerishTagTrait
{
	abstract protected function min() : int;

	abstract protected function max() : int;

	/** @var int */
	private $value;

	public function __construct(int $value)
	{
		if (func_num_args() > 1) {
			throw new \ArgumentCountError(__METHOD__ . "() expects at most 1 parameters, " . func_num_args() . " given");
		}
		if ($value < $this->min() || $value > $this->max()) {
			throw new InvalidTagValueException("Value $value is outside the allowed range " . $this->min() . " - " . $this->max());
		}
		$this->value = $value;
	}

	public function getValue() : int
	{
		return $this->value;
	}

	protected function stringifyValue(int $indentation) : string
	{
		return (string) $this->value;
	}
}
