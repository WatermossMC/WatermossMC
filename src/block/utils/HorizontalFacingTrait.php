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
use watermossmc\math\Axis;
use watermossmc\math\Facing;

trait HorizontalFacingTrait
{
	protected int $facing = Facing::NORTH;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
	}

	public function getFacing() : int
	{
		return $this->facing;
	}

	/** @return $this */
	public function setFacing(int $facing) : self
	{
		$axis = Facing::axis($facing);
		if ($axis !== Axis::X && $axis !== Axis::Z) {
			throw new \InvalidArgumentException("Facing must be horizontal");
		}
		$this->facing = $facing;
		return $this;
	}
}
