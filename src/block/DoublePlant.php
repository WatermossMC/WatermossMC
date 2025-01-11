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

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

class DoublePlant extends Flowable
{
	protected bool $top = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->top);
	}

	public function isTop() : bool
	{
		return $this->top;
	}

	/** @return $this */
	public function setTop(bool $top) : self
	{
		$this->top = $top;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$down = $blockReplace->getSide(Facing::DOWN);
		if ($down->hasTypeTag(BlockTypeTags::DIRT) && $blockReplace->getSide(Facing::UP)->canBeReplaced()) {
			$top = clone $this;
			$top->top = true;
			$tx->addBlock($blockReplace->position, $this)->addBlock($blockReplace->position->getSide(Facing::UP), $top);
			return true;
		}

		return false;
	}

	/**
	 * Returns whether this double-plant has a corresponding other half.
	 */
	public function isValidHalfPlant() : bool
	{
		$other = $this->getSide($this->top ? Facing::DOWN : Facing::UP);

		return (
			$other instanceof DoublePlant &&
			$other->hasSameTypeId($this) &&
			$other->top !== $this->top
		);
	}

	public function onNearbyBlockChange() : void
	{
		$down = $this->getSide(Facing::DOWN);
		if (!$this->isValidHalfPlant() || (!$this->top && !$down->hasTypeTag(BlockTypeTags::DIRT) && !$down->hasTypeTag(BlockTypeTags::MUD))) {
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function getDrops(Item $item) : array
	{
		return $this->top ? parent::getDrops($item) : [];
	}

	public function getAffectedBlocks() : array
	{
		if ($this->isValidHalfPlant()) {
			return [$this, $this->getSide($this->top ? Facing::DOWN : Facing::UP)];
		}

		return parent::getAffectedBlocks();
	}

	public function getFlameEncouragement() : int
	{
		return 60;
	}

	public function getFlammability() : int
	{
		return 100;
	}
}
