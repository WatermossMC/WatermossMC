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
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

final class TorchflowerCrop extends Flowable
{
	use StaticSupportTrait;

	private bool $ready = false;

	public function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->ready);
	}

	public function isReady() : bool
	{
		return $this->ready;
	}

	public function setReady(bool $ready) : self
	{
		$this->ready = $ready;
		return $this;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getSide(Facing::DOWN)->getTypeId() === BlockTypeIds::FARMLAND;
	}

	private function getNextState() : Block
	{
		if ($this->ready) {
			return VanillaBlocks::TORCHFLOWER();
		} else {
			return VanillaBlocks::TORCHFLOWER_CROP()->setReady(true);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Fertilizer) {
			if (BlockEventHelper::grow($this, $this->getNextState(), $player)) {
				$item->pop();
			}

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
			BlockEventHelper::grow($this, $this->getNextState(), null);
		}
	}

	public function asItem() : Item
	{
		return VanillaItems::TORCHFLOWER_SEEDS();
	}
}
