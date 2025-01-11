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

use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\CropGrowthHelper;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Facing;

use function array_rand;
use function mt_rand;

abstract class Stem extends Crops
{
	protected int $facing = Facing::UP;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		parent::describeBlockOnlyState($w);
		$w->facingExcept($this->facing, Facing::DOWN);
	}

	public function getFacing() : int
	{
		return $this->facing;
	}

	/** @return $this */
	public function setFacing(int $facing) : self
	{
		if ($facing === Facing::DOWN) {
			throw new \InvalidArgumentException("DOWN is not a valid facing for this block");
		}
		$this->facing = $facing;
		return $this;
	}

	abstract protected function getPlant() : Block;

	public function onNearbyBlockChange() : void
	{
		if ($this->facing !== Facing::UP && !$this->getSide($this->facing)->hasSameTypeId($this->getPlant())) {
			$this->position->getWorld()->setBlock($this->position, $this->setFacing(Facing::UP));
		}
		parent::onNearbyBlockChange();
	}

	public function ticksRandomly() : bool
	{
		return $this->age < self::MAX_AGE || $this->facing === Facing::UP;
	}

	public function onRandomTick() : void
	{
		if ($this->facing === Facing::UP && CropGrowthHelper::canGrow($this)) {
			$world = $this->position->getWorld();
			if ($this->age < self::MAX_AGE) {
				$block = clone $this;
				++$block->age;
				BlockEventHelper::grow($this, $block, null);
			} else {
				$grow = $this->getPlant();
				foreach (Facing::HORIZONTAL as $side) {
					if ($this->getSide($side)->hasSameTypeId($grow)) {
						return;
					}
				}

				$facing = Facing::HORIZONTAL[array_rand(Facing::HORIZONTAL)];
				$side = $this->getSide($facing);
				if ($side->getTypeId() === BlockTypeIds::AIR && $side->getSide(Facing::DOWN)->hasTypeTag(BlockTypeTags::DIRT)) {
					if (BlockEventHelper::grow($side, $grow, null)) {
						$this->position->getWorld()->setBlock($this->position, $this->setFacing($facing));
					}
				}
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			$this->asItem()->setCount(mt_rand(0, 2))
		];
	}
}
