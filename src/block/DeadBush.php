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
use watermossmc\math\Facing;

use function mt_rand;

class DeadBush extends Flowable
{
	use StaticSupportTrait;

	public function getDropsForIncompatibleTool(Item $item) : array
	{
		return [
			VanillaItems::STICK()->setCount(mt_rand(0, 2))
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function getFlameEncouragement() : int
	{
		return 60;
	}

	public function getFlammability() : int
	{
		return 100;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$supportBlock = $block->getSide(Facing::DOWN);
		return
			$supportBlock->hasTypeTag(BlockTypeTags::SAND) ||
			$supportBlock->hasTypeTag(BlockTypeTags::MUD) ||
			match($supportBlock->getTypeId()) {
				//can't use DIRT tag here because it includes farmland
				BlockTypeIds::PODZOL,
				BlockTypeIds::MYCELIUM,
				BlockTypeIds::DIRT,
				BlockTypeIds::GRASS,
				BlockTypeIds::HARDENED_CLAY,
				BlockTypeIds::STAINED_CLAY => true,
				//TODO: moss block
				default => false,
			};
	}
}
