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
use watermossmc\item\enchantment\VanillaEnchantments;
use watermossmc\item\Item;
use watermossmc\math\Facing;

final class HangingRoots extends Flowable
{
	use StaticSupportTrait;

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getAdjacentSupportType(Facing::UP)->hasCenterSupport(); //weird I know, but they can be placed on the bottom of fences
	}

	public function getDropsForIncompatibleTool(Item $item) : array
	{
		if ($item->hasEnchantment(VanillaEnchantments::SILK_TOUCH())) {
			return $this->getDropsForCompatibleTool($item);
		}
		return [];
	}
}
