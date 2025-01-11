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

use watermossmc\block\utils\AnyFacingTrait;
use watermossmc\item\Item;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

class EndRod extends Flowable
{
	use AnyFacingTrait;

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->facing = $face;
		if ($blockClicked instanceof EndRod && $blockClicked->facing === $this->facing) {
			$this->facing = Facing::opposite($face);
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function isSolid() : bool
	{
		return true;
	}

	public function getLightLevel() : int
	{
		return 14;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		$myAxis = Facing::axis($this->facing);

		$bb = AxisAlignedBB::one();
		foreach ([Axis::Y, Axis::Z, Axis::X] as $axis) {
			if ($axis === $myAxis) {
				continue;
			}
			$bb->squash($axis, 6 / 16);
		}
		return [$bb];
	}
}
