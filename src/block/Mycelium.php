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
use watermossmc\block\utils\DirtType;
use watermossmc\item\Item;
use watermossmc\math\Facing;

use function mt_rand;

class Mycelium extends Opaque
{
	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaBlocks::DIRT()->asItem()
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		//TODO: light levels
		$x = mt_rand($this->position->x - 1, $this->position->x + 1);
		$y = mt_rand($this->position->y - 2, $this->position->y + 2);
		$z = mt_rand($this->position->z - 1, $this->position->z + 1);
		$world = $this->position->getWorld();
		$block = $world->getBlockAt($x, $y, $z);
		if ($block instanceof Dirt && $block->getDirtType() === DirtType::NORMAL) {
			if ($block->getSide(Facing::UP) instanceof Transparent) {
				BlockEventHelper::spread($block, VanillaBlocks::MYCELIUM(), $this);
			}
		}
	}
}
