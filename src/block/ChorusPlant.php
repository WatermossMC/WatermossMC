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
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Axis;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;

use function mt_rand;

final class ChorusPlant extends Flowable
{
	use StaticSupportTrait;

	protected function recalculateCollisionBoxes() : array
	{
		$bb = AxisAlignedBB::one();
		foreach ($this->getAllSides() as $facing => $block) {
			$id = $block->getTypeId();
			if ($id !== BlockTypeIds::END_STONE && $id !== BlockTypeIds::CHORUS_FLOWER && !$block->hasSameTypeId($this)) {
				$bb->trim($facing, 2 / 16);
			}
		}

		return [$bb];
	}

	private function canBeSupportedBy(Block $block) : bool
	{
		return $block->hasSameTypeId($this) || $block->getTypeId() === BlockTypeIds::END_STONE;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$position = $block->position;
		$world = $position->getWorld();

		$down = $world->getBlock($position->down());
		$verticalAir = $down->getTypeId() === BlockTypeIds::AIR || $world->getBlock($position->up())->getTypeId() === BlockTypeIds::AIR;

		foreach ($position->sidesAroundAxis(Axis::Y) as $sidePosition) {
			$block = $world->getBlock($sidePosition);

			if ($block->getTypeId() === BlockTypeIds::CHORUS_PLANT) {
				if (!$verticalAir) {
					return false;
				}

				if ($this->canBeSupportedBy($block->getSide(Facing::DOWN))) {
					return true;
				}
			}
		}

		return $this->canBeSupportedBy($down);
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		if (mt_rand(0, 1) === 1) {
			return [VanillaItems::CHORUS_FRUIT()];
		}

		return [];
	}
}
