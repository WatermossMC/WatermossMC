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

use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

class Lantern extends Transparent
{
	private int $lightLevel; //readonly

	protected bool $hanging = false;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, int $lightLevel)
	{
		$this->lightLevel = $lightLevel;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->hanging);
	}

	public function isHanging() : bool
	{
		return $this->hanging;
	}

	/** @return $this */
	public function setHanging(bool $hanging) : self
	{
		$this->hanging = $hanging;
		return $this;
	}

	public function getLightLevel() : int
	{
		return $this->lightLevel;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [
			AxisAlignedBB::one()
				->trim(Facing::UP, $this->hanging ? 6 / 16 : 8 / 16)
				->trim(Facing::DOWN, $this->hanging ? 2 / 16 : 0)
				->squash(Axis::X, 5 / 16)
				->squash(Axis::Z, 5 / 16)
		];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$downSupport = $this->canBeSupportedAt($blockReplace, Facing::DOWN);
		if (!$downSupport && !$this->canBeSupportedAt($blockReplace, Facing::UP)) {
			return false;
		}

		$this->hanging = $face === Facing::DOWN || !$downSupport;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void
	{
		$face = $this->hanging ? Facing::UP : Facing::DOWN;
		if (!$this->canBeSupportedAt($this, $face)) {
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	private function canBeSupportedAt(Block $block, int $face) : bool
	{
		return $block->getAdjacentSupportType($face)->hasCenterSupport();
	}
}
