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

namespace watermossmc\block\utils;

use watermossmc\data\runtime\RuntimeDataDescriber;

/**
 * This trait is used for blocks that have an age property.
 * Need to add to the block the constant MAX_AGE.
 */
trait AgeableTrait
{
	protected int $age = 0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(0, self::MAX_AGE, $this->age);
	}

	public function getAge() : int
	{
		return $this->age;
	}

	/**
	 * @return $this
	 */
	public function setAge(int $age) : self
	{
		if ($age < 0 || $age > self::MAX_AGE) {
			throw new \InvalidArgumentException("Age must be in range 0 ... " . self::MAX_AGE);
		}
		$this->age = $age;
		return $this;
	}
}
