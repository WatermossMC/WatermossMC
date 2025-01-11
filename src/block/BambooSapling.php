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

use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\event\block\StructureGrowEvent;
use watermossmc\item\Bamboo as ItemBamboo;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

final class BambooSapling extends Flowable
{
	use StaticSupportTrait;

	private bool $ready = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->ready);
	}

	public function isReady() : bool
	{
		return $this->ready;
	}

	/** @return $this */
	public function setReady(bool $ready) : self
	{
		$this->ready = $ready;
		return $this;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			$supportBlock->getTypeId() === BlockTypeIds::GRAVEL ||
			$supportBlock->hasTypeTag(BlockTypeTags::DIRT) ||
			$supportBlock->hasTypeTag(BlockTypeTags::MUD) ||
			$supportBlock->hasTypeTag(BlockTypeTags::SAND);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Fertilizer || $item instanceof ItemBamboo) {
			if ($this->grow($player)) {
				$item->pop();
				return true;
			}
		}
		return false;
	}

	private function grow(?Player $player) : bool
	{
		$world = $this->position->getWorld();
		if (!$world->getBlock($this->position->up())->canBeReplaced()) {
			return false;
		}

		$tx = new BlockTransaction($world);
		$bamboo = VanillaBlocks::BAMBOO();
		$tx->addBlock($this->position, $bamboo)
			->addBlock($this->position->up(), (clone $bamboo)->setLeafSize(Bamboo::SMALL_LEAVES));

		$ev = new StructureGrowEvent($this, $tx, $player);
		$ev->call();
		if ($ev->isCancelled()) {
			return false;
		}

		return $tx->apply();
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$world = $this->position->getWorld();
		if ($this->ready) {
			$this->ready = false;
			if ($world->getFullLight($this->position) < 9 || !$this->grow(null)) {
				$world->setBlock($this->position, $this);
			}
		} elseif ($world->getBlock($this->position->up())->canBeReplaced()) {
			$this->ready = true;
			$world->setBlock($this->position, $this);
		}
	}

	public function asItem() : Item
	{
		return VanillaItems::BAMBOO();
	}
}
