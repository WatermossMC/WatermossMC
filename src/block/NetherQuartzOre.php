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

class NetherQuartzOre extends Opaque
{
	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaItems::NETHER_QUARTZ()->setCount(FortuneDropHelper::weighted($item, min: 1, maxBase: 1))
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	protected function getXpDropAmount() : int
	{
		return mt_rand(2, 5);
	}
}
