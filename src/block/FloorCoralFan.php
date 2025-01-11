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
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Axis;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

use function atan2;
use function rad2deg;

final class FloorCoralFan extends BaseCoral
{
	use StaticSupportTrait;

	private int $axis = Axis::X;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalAxis($this->axis);
	}

	public function getAxis() : int
	{
		return $this->axis;
	}

	/** @return $this */
	public function setAxis(int $axis) : self
	{
		if ($axis !== Axis::X && $axis !== Axis::Z) {
			throw new \InvalidArgumentException("Axis must be X or Z only");
		}
		$this->axis = $axis;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($player !== null) {
			$playerBlockPos = $player->getPosition()->floor();
			$directionVector = $blockReplace->position->subtractVector($playerBlockPos)->normalize();
			$angle = rad2deg(atan2($directionVector->getZ(), $directionVector->getX()));

			if ($angle <= 45 || 315 <= $angle || (135 <= $angle && $angle <= 225)) {
				//TODO: This produces Z axis 75% of the time, because any negative angle will produce Z axis.
				//This is a bug in vanilla. https://bugs.mojang.com/browse/MCPE-125311
				$this->axis = Axis::Z;
			}
		}

		$this->dead = !$this->isCoveredWithWater();

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getAdjacentSupportType(Facing::DOWN)->hasCenterSupport();
	}

	public function asItem() : Item
	{
		return VanillaItems::CORAL_FAN()->setCoralType($this->coralType)->setDead($this->dead);
	}
}
