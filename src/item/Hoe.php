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

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\item;

use watermossmc\block\Block;
use watermossmc\block\BlockToolType;
use watermossmc\entity\Entity;

class Hoe extends TieredTool
{
	public function getBlockToolType() : int
	{
		return BlockToolType::HOE;
	}

	public function onAttackEntity(Entity $victim, array &$returnedItems) : bool
	{
		return $this->applyDamage(1);
	}

	public function onDestroyBlock(Block $block, array &$returnedItems) : bool
	{
		if (!$block->getBreakInfo()->breaksInstantly()) {
			return $this->applyDamage(1);
		}
		return false;
	}
}
