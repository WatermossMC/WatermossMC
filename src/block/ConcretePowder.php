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

use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\ColoredTrait;
use watermossmc\block\utils\Fallable;
use watermossmc\block\utils\FallableTrait;
use watermossmc\math\Facing;

class ConcretePowder extends Opaque implements Fallable
{
	use ColoredTrait;
	use FallableTrait {
		onNearbyBlockChange as protected startFalling;
	}

	public function onNearbyBlockChange() : void
	{
		if (($water = $this->getAdjacentWater()) !== null) {
			BlockEventHelper::form($this, VanillaBlocks::CONCRETE()->setColor($this->color), $water);
		} else {
			$this->startFalling();
		}
	}

	public function tickFalling() : ?Block
	{
		if ($this->getAdjacentWater() === null) {
			return null;
		}
		return VanillaBlocks::CONCRETE()->setColor($this->color);
	}

	private function getAdjacentWater() : ?Water
	{
		foreach (Facing::ALL as $i) {
			if ($i === Facing::DOWN) {
				continue;
			}
			$block = $this->getSide($i);
			if ($block instanceof Water) {
				return $block;
			}
		}

		return null;
	}
}
