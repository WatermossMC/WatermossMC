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

use watermossmc\entity\Entity;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;

class Cobweb extends Flowable
{
	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		$entity->resetFallDistance();
		return true;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		if (($item->getBlockToolType() & BlockToolType::SHEARS) !== 0) {
			return [$this->asItem()];
		}
		return [
			VanillaItems::STRING()
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function blocksDirectSkyLight() : bool
	{
		return true;
	}
}
