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

use function mt_rand;

class Potato extends Crops
{
	public function getDropsForCompatibleTool(Item $item) : array
	{
		$result = [
			//min/max would be 2-5 in Java
			VanillaItems::POTATO()->setCount($this->age >= self::MAX_AGE ? FortuneDropHelper::binomial($item, 1) : 1)
		];
		if ($this->age >= self::MAX_AGE && mt_rand(0, 49) === 0) {
			$result[] = VanillaItems::POISONOUS_POTATO();
		}
		return $result;
	}

	public function asItem() : Item
	{
		return VanillaItems::POTATO();
	}
}
