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

use watermossmc\block\utils\SaplingType;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\event\block\StructureGrowEvent;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\utils\Random;
use watermossmc\world\generator\object\TreeFactory;

use function mt_rand;

class Sapling extends Flowable
{
	use StaticSupportTrait;

	protected bool $ready = false;

	private SaplingType $saplingType;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, SaplingType $saplingType)
	{
		parent::__construct($idInfo, $name, $typeInfo);
		$this->saplingType = $saplingType;
	}

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
		return $supportBlock->hasTypeTag(BlockTypeTags::DIRT) || $supportBlock->hasTypeTag(BlockTypeTags::MUD);
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
		$world = $this->position->getWorld();
		if ($world->getFullLightAt($this->position->getFloorX(), $this->position->getFloorY(), $this->position->getFloorZ()) >= 8 && mt_rand(1, 7) === 1) {
			if ($this->ready) {
				$this->grow(null);
			} else {
				$this->ready = true;
				$world->setBlock($this->position, $this);
			}
		}
	}

	private function grow(?Player $player) : bool
	{
		$random = new Random(mt_rand());
		$tree = TreeFactory::get($random, $this->saplingType->getTreeType());
		$transaction = $tree?->getBlockTransaction($this->position->getWorld(), $this->position->getFloorX(), $this->position->getFloorY(), $this->position->getFloorZ(), $random);
		if ($transaction === null) {
			return false;
		}

		$ev = new StructureGrowEvent($this, $transaction, $player);
		$ev->call();
		if (!$ev->isCancelled()) {
			return $transaction->apply();
		}
		return false;
	}

	public function getFuelTime() : int
	{
		return 100;
	}
}
