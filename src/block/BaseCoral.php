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

use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\CoralTypeTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\item\Item;

use function mt_rand;

abstract class BaseCoral extends Transparent
{
	use CoralTypeTrait;

	public function onNearbyBlockChange() : void
	{
		if (!$this->dead) {
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, mt_rand(40, 200));
		}
	}

	public function onScheduledUpdate() : void
	{
		if (!$this->dead && !$this->isCoveredWithWater()) {
			BlockEventHelper::die($this, (clone $this)->setDead(true));
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function isSolid() : bool
	{
		return false;
	}

	protected function isCoveredWithWater() : bool
	{
		$world = $this->position->getWorld();

		$hasWater = false;
		foreach ($this->position->sides() as $vector3) {
			if ($world->getBlock($vector3) instanceof Water) {
				$hasWater = true;
				break;
			}
		}

		//TODO: check water inside the block itself (not supported on the API yet)
		return $hasWater;
	}

	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}
}
