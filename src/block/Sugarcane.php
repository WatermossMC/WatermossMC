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
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;
use watermossmc\world\Position;

class Sugarcane extends Flowable
{
	use AgeableTrait;
	use StaticSupportTrait {
		onNearbyBlockChange as onSupportBlockChange;
	}

	public const MAX_AGE = 15;

	private function seekToBottom() : Position
	{
		$world = $this->position->getWorld();
		$bottom = $this->position;
		while (($next = $world->getBlock($bottom->down()))->hasSameTypeId($this)) {
			$bottom = $next->position;
		}
		return $bottom;
	}

	private function grow(Position $pos, ?Player $player = null) : bool
	{
		$grew = false;
		$world = $pos->getWorld();
		for ($y = 1; $y < 3; ++$y) {
			if (!$world->isInWorld($pos->x, $pos->y + $y, $pos->z)) {
				break;
			}
			$b = $world->getBlockAt($pos->x, $pos->y + $y, $pos->z);
			if ($b->getTypeId() === BlockTypeIds::AIR) {
				if (BlockEventHelper::grow($b, VanillaBlocks::SUGARCANE(), $player)) {
					$grew = true;
				} else {
					break;
				}
			} elseif (!$b->hasSameTypeId($this)) {
				break;
			}
		}
		$this->age = 0;
		$world->setBlock($pos, $this);
		return $grew;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Fertilizer) {
			if ($this->grow($this->seekToBottom(), $player)) {
				$item->pop();
			}

			return true;
		}

		return false;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$supportBlock = $block->getSide(Facing::DOWN);
		return $supportBlock->hasSameTypeId($this) ||
			$supportBlock->hasTypeTag(BlockTypeTags::MUD) ||
			$supportBlock->hasTypeTag(BlockTypeTags::DIRT) ||
			$supportBlock->hasTypeTag(BlockTypeTags::SAND);
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$down = $this->getSide(Facing::DOWN);
		if (!$down->hasSameTypeId($this)) {
			if (!$this->hasNearbyWater($down)) {
				$this->position->getWorld()->useBreakOn($this->position, createParticles: true);
				return;
			}

			if ($this->age === self::MAX_AGE) {
				$this->grow($this->position);
			} else {
				++$this->age;
				$this->position->getWorld()->setBlock($this->position, $this);
			}
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$down = $blockReplace->getSide(Facing::DOWN);
		if ($down->hasSameTypeId($this)) {
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		//support criteria are checked by FixedSupportTrait, but this part applies to placement only
		foreach (Facing::HORIZONTAL as $side) {
			$sideBlock = $down->getSide($side);
			if ($sideBlock instanceof Water || $sideBlock instanceof FrostedIce) {
				return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
			}
		}

		return false;
	}

	private function hasNearbyWater(Block $down) : bool
	{
		foreach ($down->getHorizontalSides() as $sideBlock) {
			$blockId = $sideBlock->getTypeId();
			if ($blockId === BlockTypeIds::WATER || $blockId === BlockTypeIds::FROSTED_ICE) {
				return true;
			}
		}
		return false;
	}

	public function onNearbyBlockChange() : void
	{
		$down = $this->getSide(Facing::DOWN);
		if (!$down->hasSameTypeId($this) && !$this->hasNearbyWater($down)) {
			$this->position->getWorld()->useBreakOn($this->position, createParticles: true);
		} else {
			$this->onSupportBlockChange();
		}
	}
}
