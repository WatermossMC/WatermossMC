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

use watermossmc\crafting\FurnaceType;
use watermossmc\item\Item;

class SoulCampfire extends Campfire
{
	public function getLightLevel() : int
	{
		return $this->lit ? 10 : 0;
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaBlocks::SOUL_SOIL()->asItem()
		];
	}

	protected function getEntityCollisionDamage() : int
	{
		return 2;
	}

	protected function getFurnaceType() : FurnaceType
	{
		return FurnaceType::SOUL_CAMPFIRE;
	}
}
