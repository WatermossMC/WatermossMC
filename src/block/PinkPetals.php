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
use watermossmc\block\utils\HorizontalFacingTrait;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

class PinkPetals extends Flowable
{
	use HorizontalFacingTrait;
	use StaticSupportTrait {
		canBePlacedAt as supportedWhenPlacedAt;
	}

	public const MIN_COUNT = 1;
	public const MAX_COUNT = 4;

	protected int $count = self::MIN_COUNT;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
		$w->boundedIntAuto(self::MIN_COUNT, self::MAX_COUNT, $this->count);
	}

	public function getCount() : int
	{
		return $this->count;
	}

	/** @return $this */
	public function setCount(int $count) : self
	{
		if ($count < self::MIN_COUNT || $count > self::MAX_COUNT) {
			throw new \InvalidArgumentException("Count must be in range " . self::MIN_COUNT . " ... " . self::MAX_COUNT);
		}
		$this->count = $count;
		return $this;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$supportBlock = $block->getSide(Facing::DOWN);
		//TODO: Moss block
		return $supportBlock->hasTypeTag(BlockTypeTags::DIRT) || $supportBlock->hasTypeTag(BlockTypeTags::MUD);
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool
	{
		return ($blockReplace instanceof PinkPetals && $blockReplace->count < self::MAX_COUNT) || $this->supportedWhenPlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($blockReplace instanceof PinkPetals && $blockReplace->count < self::MAX_COUNT) {
			$this->count = $blockReplace->count + 1;
			$this->facing = $blockReplace->facing;
		} elseif ($player !== null) {
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Fertilizer) {
			$grew = false;
			if ($this->count < self::MAX_COUNT) {
				$grew = BlockEventHelper::grow($this, (clone $this)->setCount($this->count + 1), $player);
			} else {
				$this->position->getWorld()->dropItem($this->position->add(0, 0.5, 0), $this->asItem());
				$grew = true;
			}
			if ($grew) {
				$item->pop();
				return true;
			}
		}
		return false;
	}

	public function getFlameEncouragement() : int
	{
		return 60;
	}

	public function getFlammability() : int
	{
		return 100;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [$this->asItem()->setCount($this->count)];
	}
}
