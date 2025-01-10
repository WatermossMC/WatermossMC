<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

namespace watermossmc\block;

use watermossmc\block\utils\FortuneDropHelper;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;

use function mt_rand;

final class GildedBlackstone extends Opaque
{
	public function getDropsForCompatibleTool(Item $item) : array
	{
		if (FortuneDropHelper::bonusChanceDivisor($item, 10, 3)) {
			return [VanillaItems::GOLD_NUGGET()->setCount(mt_rand(2, 5))];
		}

		return parent::getDropsForCompatibleTool($item);
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}
}
