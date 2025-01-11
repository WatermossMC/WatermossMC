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

use watermossmc\block\utils\AgeableTrait;
use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\HorizontalFacingTrait;
use watermossmc\block\utils\WoodType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

use function mt_rand;

class CocoaBlock extends Flowable
{
	use HorizontalFacingTrait;
	use AgeableTrait;

	public const MAX_AGE = 2;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
		$w->boundedIntAuto(0, self::MAX_AGE, $this->age);
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [
			AxisAlignedBB::one()
				->squash(Facing::axis(Facing::rotateY($this->facing, true)), (6 - $this->age) / 16) //sides
				->trim(Facing::DOWN, (7 - $this->age * 2) / 16)
				->trim(Facing::UP, 0.25)
				->trim(Facing::opposite($this->facing), 1 / 16) //gap between log and pod
				->trim($this->facing, (11 - $this->age * 2) / 16) //outward face
		];
	}

	private function canAttachTo(Block $block) : bool
	{
		return $block instanceof Wood && $block->getWoodType() === WoodType::JUNGLE;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if (Facing::axis($face) !== Axis::Y && $this->canAttachTo($blockClicked)) {
			$this->facing = $face;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Fertilizer && $this->grow($player)) {
			$item->pop();

			return true;
		}

		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->canAttachTo($this->getSide(Facing::opposite($this->facing)))) {
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function ticksRandomly() : bool
	{
		return $this->age < self::MAX_AGE;
	}

	public function onRandomTick() : void
	{
		if (mt_rand(1, 5) === 1) {
			$this->grow();
		}
	}

	private function grow(?Player $player = null) : bool
	{
		if ($this->age < self::MAX_AGE) {
			$block = clone $this;
			$block->age++;
			return BlockEventHelper::grow($this, $block, $player);
		}
		return false;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaItems::COCOA_BEANS()->setCount($this->age === self::MAX_AGE ? mt_rand(2, 3) : 1)
		];
	}

	public function asItem() : Item
	{
		return VanillaItems::COCOA_BEANS();
	}
}
