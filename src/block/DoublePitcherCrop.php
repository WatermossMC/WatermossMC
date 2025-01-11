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
use watermossmc\block\utils\CropGrowthHelper;
use watermossmc\data\runtime\RuntimeDataDescriber;
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

final class DoublePitcherCrop extends DoublePlant
{
	use AgeableTrait {
		describeBlockOnlyState as describeAge;
	}

	public const MAX_AGE = 1;

	public function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		parent::describeBlockOnlyState($w);
		$this->describeAge($w);
	}

	protected function recalculateCollisionBoxes() : array
	{
		if ($this->top) {
			return [];
		}

		//the pod exists only in the bottom half of the plant
		return [
			AxisAlignedBB::one()
			->trim(Facing::UP, 11 / 16)
			->squash(Axis::X, 3 / 16)
			->squash(Axis::Z, 3 / 16)
			->extend(Facing::DOWN, 1 / 16) //presumably this is to correct for farmland being 15/16 of a block tall
		];
	}

	private function grow(?Player $player) : bool
	{
		if ($this->age >= self::MAX_AGE) {
			return false;
		}

		$bottom = $this->top ? $this->getSide(Facing::DOWN) : $this;
		$top = $this->top ? $this : $this->getSide(Facing::UP);
		if ($top->getTypeId() !== BlockTypeIds::AIR && !$top->hasSameTypeId($this)) {
			return false;
		}

		$newState = (clone $this)->setAge($this->age + 1);

		$tx = new BlockTransaction($this->position->getWorld());
		$tx->addBlock($bottom->position, (clone $newState)->setTop(false));
		$tx->addBlock($top->position, (clone $newState)->setTop(true));

		$ev = new StructureGrowEvent($bottom, $tx, $player);
		$ev->call();

		return !$ev->isCancelled() && $tx->apply();

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
		return $this->age < self::MAX_AGE && !$this->top;
	}

	public function onRandomTick() : void
	{
		//only the bottom half of the plant can grow randomly
		if (CropGrowthHelper::canGrow($this) && !$this->top) {
			$this->grow(null);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			$this->age >= self::MAX_AGE ? VanillaBlocks::PITCHER_PLANT()->asItem() : VanillaItems::PITCHER_POD()
		];
	}

	public function asItem() : Item
	{
		return VanillaItems::PITCHER_POD();
	}
}
