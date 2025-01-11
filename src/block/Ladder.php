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

use watermossmc\block\utils\HorizontalFacingTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\entity\Entity;
use watermossmc\entity\Living;
use watermossmc\item\Item;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

class Ladder extends Transparent
{
	use HorizontalFacingTrait;

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function isSolid() : bool
	{
		return false;
	}

	public function canClimb() : bool
	{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		if ($entity instanceof Living && $entity->getPosition()->floor()->distanceSquared($this->position) < 1) { //entity coordinates must be inside block
			$entity->resetFallDistance();
			$entity->onGround = true;
		}
		return true;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [AxisAlignedBB::one()->trim($this->facing, 13 / 16)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($this->canBeSupportedAt($blockReplace, Facing::opposite($face)) && Facing::axis($face) !== Axis::Y) {
			$this->facing = $face;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->canBeSupportedAt($this, Facing::opposite($this->facing))) { //Replace with common break method
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	private function canBeSupportedAt(Block $block, int $face) : bool
	{
		return $block->getAdjacentSupportType($face) === SupportType::FULL;
	}
}
