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
