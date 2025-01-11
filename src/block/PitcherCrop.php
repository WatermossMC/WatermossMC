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
use watermossmc\block\utils\CropGrowthHelper;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\event\block\StructureGrowEvent;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

final class PitcherCrop extends Flowable
{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 2;

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getSide(Facing::DOWN)->getTypeId() === BlockTypeIds::FARMLAND;
	}

	protected function recalculateCollisionBoxes() : array
	{
		$widthTrim = $this->age === 0 ? 5 : 3;
		$heightTrim = $this->age === 0 ? 13 : 11;
		return [
			AxisAlignedBB::one()
				->trim(Facing::UP, $heightTrim / 16)
				->squash(Axis::X, $widthTrim / 16)
				->squash(Axis::Z, $widthTrim / 16)
				->extend(Facing::DOWN, 1 / 16) //presumably this is to correct for farmland being 15/16 of a block tall
		];
	}

	private function grow(?Player $player) : bool
	{
		if ($this->age > self::MAX_AGE) {
			return false;
		}

		if ($this->age === self::MAX_AGE) {
			$up = $this->getSide(Facing::UP);
			if ($up->getTypeId() !== BlockTypeIds::AIR) {
				return false;
			}

			$tx = new BlockTransaction($this->position->getWorld());
			$tx->addBlock($this->position, VanillaBlocks::DOUBLE_PITCHER_CROP()->setTop(false));
			$tx->addBlock($this->position->up(), VanillaBlocks::DOUBLE_PITCHER_CROP()->setTop(true));

			$ev = new StructureGrowEvent($this, $tx, $player);
			$ev->call();

			return !$ev->isCancelled() && $tx->apply();
		}

		return BlockEventHelper::grow($this, (clone $this)->setAge($this->age + 1), $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Fertilizer && $this->grow($player)) {
			$item->pop();
			return true;
		}

		return false;
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if (CropGrowthHelper::canGrow($this)) {
			$this->grow(null);
		}
	}

	public function asItem() : Item
	{
		return VanillaItems::PITCHER_POD();
	}
}
