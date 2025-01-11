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
use watermossmc\block\utils\CropGrowthHelper;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

use function mt_rand;

abstract class Crops extends Flowable
{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 7;

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getSide(Facing::DOWN)->getTypeId() === BlockTypeIds::FARMLAND;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($this->age < self::MAX_AGE && $item instanceof Fertilizer) {
			$block = clone $this;
			$tempAge = $block->age + mt_rand(2, 5);
			if ($tempAge > self::MAX_AGE) {
				$tempAge = self::MAX_AGE;
			}
			$block->age = $tempAge;
			if (BlockEventHelper::grow($this, $block, $player)) {
				$item->pop();
			}

			return true;
		}

		return false;
	}

	public function ticksRandomly() : bool
	{
		return $this->age < self::MAX_AGE;
	}

	public function onRandomTick() : void
	{
		if ($this->age < self::MAX_AGE && CropGrowthHelper::canGrow($this)) {
			$block = clone $this;
			++$block->age;
			BlockEventHelper::grow($this, $block, null);
		}
	}
}
