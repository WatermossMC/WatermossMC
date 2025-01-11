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
use watermossmc\item\enchantment\VanillaEnchantments;
use watermossmc\item\Item;
use watermossmc\player\Player;

class Ice extends Transparent
{
	public function getLightFilter() : int
	{
		return 2;
	}

	public function getFrictionFactor() : float
	{
		return 0.98;
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if (($player === null || $player->isSurvival()) && !$item->hasEnchantment(VanillaEnchantments::SILK_TOUCH())) {
			$this->position->getWorld()->setBlock($this->position, VanillaBlocks::WATER());
			return true;
		}
		return parent::onBreak($item, $player, $returnedItems);
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$world = $this->position->getWorld();
		if ($world->getHighestAdjacentBlockLight($this->position->x, $this->position->y, $this->position->z) >= 12) {
			BlockEventHelper::melt($this, VanillaBlocks::WATER());
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
}
