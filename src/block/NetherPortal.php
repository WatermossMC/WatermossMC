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

use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\entity\Entity;
use watermossmc\item\Item;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;

class NetherPortal extends Transparent
{
	protected int $axis = Axis::X;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalAxis($this->axis);
	}

	public function getAxis() : int
	{
		return $this->axis;
	}

	/**
	 * @throws \InvalidArgumentException
	 * @return $this
	 */
	public function setAxis(int $axis) : self
	{
		if ($axis !== Axis::X && $axis !== Axis::Z) {
			throw new \InvalidArgumentException("Invalid axis");
		}
		$this->axis = $axis;
		return $this;
	}

	public function getLightLevel() : int
	{
		return 11;
	}

	public function isSolid() : bool
	{
		return false;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function getDrops(Item $item) : array
	{
		return [];
	}

	public function onEntityInside(Entity $entity) : bool
	{
		//TODO
		return true;
	}
}
