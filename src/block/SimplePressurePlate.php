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

namespace watermossmc\block;

use watermossmc\data\runtime\RuntimeDataDescriber;

use function count;

abstract class SimplePressurePlate extends PressurePlate
{
	protected bool $pressed = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->pressed);
	}

	public function isPressed() : bool
	{
		return $this->pressed;
	}

	/** @return $this */
	public function setPressed(bool $pressed) : self
	{
		$this->pressed = $pressed;
		return $this;
	}

	protected function hasOutputSignal() : bool
	{
		return $this->pressed;
	}

	protected function calculatePlateState(array $entities) : array
	{
		$newPressed = count($entities) > 0;
		if ($newPressed === $this->pressed) {
			return [$this, null];
		}
		return [
			(clone $this)->setPressed($newPressed),
			$newPressed
		];
	}
}
