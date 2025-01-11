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
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

class Torch extends Flowable
{
	protected int $facing = Facing::UP;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
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
			throw new \InvalidArgumentException("Torch may not face DOWN");
		}
		$this->facing = $facing;
		return $this;
	}

	public function getLightLevel() : int
	{
		return 14;
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->canBeSupportedAt($this, Facing::opposite($this->facing))) {
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($face !== Facing::DOWN && $this->canBeSupportedAt($blockReplace, Facing::opposite($face))) {
			$this->facing = $face;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		} else {
			foreach ([
				Facing::SOUTH,
				Facing::WEST,
				Facing::NORTH,
				Facing::EAST,
				Facing::DOWN
			] as $side) {
				if ($this->canBeSupportedAt($blockReplace, $side)) {
					$this->facing = Facing::opposite($side);
					return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
				}
			}
		}
		return false;
	}

	private function canBeSupportedAt(Block $block, int $face) : bool
	{
		return $face === Facing::DOWN ?
			$block->getAdjacentSupportType($face)->hasCenterSupport() :
			$block->getAdjacentSupportType($face) === SupportType::FULL;
	}
}
