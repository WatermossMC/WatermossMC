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

class Sword extends TieredTool
{
	public function getBlockToolType() : int
	{
		return BlockToolType::SWORD;
	}

	public function getAttackPoints() : int
	{
		return $this->tier->getBaseAttackPoints();
	}

	public function getBlockToolHarvestLevel() : int
	{
		return 1;
	}

	public function getMiningEfficiency(bool $isCorrectTool) : float
	{
		return parent::getMiningEfficiency($isCorrectTool) * 1.5; //swords break any block 1.5x faster than hand
	}

	protected function getBaseMiningEfficiency() : float
	{
		return 10;
	}

	public function onDestroyBlock(Block $block, array &$returnedItems) : bool
	{
		if (!$block->getBreakInfo()->breaksInstantly()) {
			return $this->applyDamage(2);
		}
		return false;
	}

	public function onAttackEntity(Entity $victim, array &$returnedItems) : bool
	{
		return $this->applyDamage(1);
	}
}
