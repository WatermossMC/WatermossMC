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
use watermossmc\block\BlockTypeIds;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

class GlassBottle extends Item
{
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult
	{
		if ($blockClicked->getTypeId() === BlockTypeIds::WATER) {
			$this->pop();
			$returnedItems[] = VanillaItems::POTION()->setType(PotionType::WATER);

			return ItemUseResult::SUCCESS;
		}

		return ItemUseResult::NONE;
	}
}
