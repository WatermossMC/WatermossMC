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
use watermossmc\block\utils\WoodTypeTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;
use watermossmc\world\sound\DoorSound;

class FenceGate extends Transparent
{
	use WoodTypeTrait;
	use HorizontalFacingTrait;

	protected bool $open = false;
	protected bool $inWall = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
		$w->bool($this->open);
		$w->bool($this->inWall);
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

	public function isInWall() : bool
	{
		return $this->inWall;
	}

	/** @return $this */
	public function setInWall(bool $inWall) : self
	{
		$this->inWall = $inWall;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return $this->open ? [] : [AxisAlignedBB::one()->extend(Facing::UP, 0.5)->squash(Facing::axis($this->facing), 6 / 16)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	private function checkInWall() : bool
	{
		return (
			$this->getSide(Facing::rotateY($this->facing, false)) instanceof Wall ||
			$this->getSide(Facing::rotateY($this->facing, true)) instanceof Wall
		);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($player !== null) {
			$this->facing = $player->getHorizontalFacing();
		}

		$this->inWall = $this->checkInWall();

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void
	{
		$inWall = $this->checkInWall();
		if ($inWall !== $this->inWall) {
			$this->inWall = $inWall;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		$this->open = !$this->open;
		if ($this->open && $player !== null) {
			$playerFacing = $player->getHorizontalFacing();
			if ($playerFacing === Facing::opposite($this->facing)) {
				$this->facing = $playerFacing;
			}
		}

		$world = $this->position->getWorld();
		$world->setBlock($this->position, $this);
		$world->addSound($this->position, new DoorSound());
		return true;
	}

	public function getFuelTime() : int
	{
		return $this->woodType->isFlammable() ? 300 : 0;
	}

	public function getFlameEncouragement() : int
	{
		return $this->woodType->isFlammable() ? 5 : 0;
	}

	public function getFlammability() : int
	{
		return $this->woodType->isFlammable() ? 20 : 0;
	}
}
