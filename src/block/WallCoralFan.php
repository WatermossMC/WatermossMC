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
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Axis;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

final class WallCoralFan extends BaseCoral
{
	use HorizontalFacingTrait;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$axis = Facing::axis($face);
		if (($axis !== Axis::X && $axis !== Axis::Z) || !$this->canBeSupportedAt($blockReplace, Facing::opposite($face))) {
			return false;
		}
		$this->facing = $face;

		$this->dead = !$this->isCoveredWithWater();

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void
	{
		$world = $this->position->getWorld();
		if (!$this->canBeSupportedAt($this, Facing::opposite($this->facing))) {
			$world->useBreakOn($this->position);
		} else {
			parent::onNearbyBlockChange();
		}
	}

	private function canBeSupportedAt(Block $block, int $face) : bool
	{
		return $block->getAdjacentSupportType($face)->hasCenterSupport();
	}

	public function asItem() : Item
	{
		return VanillaItems::CORAL_FAN()->setCoralType($this->coralType)->setDead($this->dead);
	}
}
