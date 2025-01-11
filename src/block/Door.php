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
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;
use watermossmc\world\sound\DoorSound;

class Door extends Transparent
{
	use HorizontalFacingTrait;

	protected bool $top = false;
	protected bool $hingeRight = false;
	protected bool $open = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
		$w->bool($this->top);
		$w->bool($this->hingeRight);
		$w->bool($this->open);
	}

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();

		$this->collisionBoxes = null;

		//copy door properties from other half
		$other = $this->getSide($this->top ? Facing::DOWN : Facing::UP);
		if ($other instanceof Door && $other->hasSameTypeId($this)) {
			if ($this->top) {
				$this->facing = $other->facing;
				$this->open = $other->open;
			} else {
				$this->hingeRight = $other->hingeRight;
			}
		}

		return $this;
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

	public function isHingeRight() : bool
	{
		return $this->hingeRight;
	}

	/** @return $this */
	public function setHingeRight(bool $hingeRight) : self
	{
		$this->hingeRight = $hingeRight;
		return $this;
	}

	public function isOpen() : bool
	{
		return $this->open;
	}

	/** @return $this */
	public function setOpen(bool $open) : self
	{
		$this->open = $open;
		return $this;
	}

	public function isSolid() : bool
	{
		return false;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		//TODO: doors are 0.1825 blocks thick, instead of 0.1875 like JE (https://bugs.mojang.com/browse/MCPE-19214)
		return [AxisAlignedBB::one()->trim($this->open ? Facing::rotateY($this->facing, !$this->hingeRight) : $this->facing, 327 / 400)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->canBeSupportedAt($this) && !$this->getSide(Facing::DOWN) instanceof Door) { //Replace with common break method
			$this->position->getWorld()->useBreakOn($this->position); //this will delete both halves if they exist
		}
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($face === Facing::UP) {
			$blockUp = $this->getSide(Facing::UP);
			if (!$blockUp->canBeReplaced() || !$this->canBeSupportedAt($blockReplace)) {
				return false;
			}

			if ($player !== null) {
				$this->facing = $player->getHorizontalFacing();
			}

			$next = $this->getSide(Facing::rotateY($this->facing, false));
			$next2 = $this->getSide(Facing::rotateY($this->facing, true));

			if ($next->hasSameTypeId($this) || (!$next2->isTransparent() && $next->isTransparent())) { //Door hinge
				$this->hingeRight = true;
			}

			$topHalf = clone $this;
			$topHalf->top = true;

			$tx->addBlock($blockReplace->position, $this)->addBlock($blockUp->position, $topHalf);
			return true;
		}

		return false;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$this->open = !$this->open;

		$other = $this->getSide($this->top ? Facing::DOWN : Facing::UP);
		$world = $this->position->getWorld();
		if ($other instanceof Door && $other->hasSameTypeId($this)) {
			$other->open = $this->open;
			$world->setBlock($other->position, $other);
		}

		$world->setBlock($this->position, $this);
		$world->addSound($this->position, new DoorSound());

		return true;
	}

	public function getDrops(Item $item) : array
	{
		if (!$this->top) {
			return parent::getDrops($item);
		}

		return [];
	}

	public function getAffectedBlocks() : array
	{
		$other = $this->getSide($this->top ? Facing::DOWN : Facing::UP);
		if ($other->hasSameTypeId($this)) {
			return [$this, $other];
		}
		return parent::getAffectedBlocks();
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getAdjacentSupportType(Facing::DOWN)->hasEdgeSupport();
	}
}
