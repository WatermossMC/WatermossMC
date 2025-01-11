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

use watermossmc\block\utils\FortuneDropHelper;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;

class Wheat extends Crops
{
	public function getDropsForCompatibleTool(Item $item) : array
	{
		if ($this->age >= self::MAX_AGE) {
			return [
				VanillaItems::WHEAT(),
				VanillaItems::WHEAT_SEEDS()->setCount(FortuneDropHelper::binomial($item, 0))
			];
		} else {
			return [
				VanillaItems::WHEAT_SEEDS()
			];
		}
	}

	public function asItem() : Item
	{
		return VanillaItems::WHEAT_SEEDS();
	}
}
