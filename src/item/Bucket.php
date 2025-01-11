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
use watermossmc\block\Liquid;
use watermossmc\block\VanillaBlocks;
use watermossmc\event\player\PlayerBucketFillEvent;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

class Bucket extends Item
{
	public function getMaxStackSize() : int
	{
		return 16;
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult
	{
		//TODO: move this to generic placement logic
		if ($blockClicked instanceof Liquid && $blockClicked->isSource()) {
			$stack = clone $this;
			$stack->pop();

			$resultItem = match($blockClicked->getTypeId()) {
				BlockTypeIds::LAVA => VanillaItems::LAVA_BUCKET(),
				BlockTypeIds::WATER => VanillaItems::WATER_BUCKET(),
				default => null
			};
			if ($resultItem === null) {
				return ItemUseResult::FAIL;
			}

			$ev = new PlayerBucketFillEvent($player, $blockReplace, $face, $this, $resultItem);
			$ev->call();
			if (!$ev->isCancelled()) {
				$player->getWorld()->setBlock($blockClicked->getPosition(), VanillaBlocks::AIR());
				$player->getWorld()->addSound($blockClicked->getPosition()->add(0.5, 0.5, 0.5), $blockClicked->getBucketFillSound());

				$this->pop();
				$returnedItems[] = $ev->getItem();
				return ItemUseResult::SUCCESS;
			}

			return ItemUseResult::FAIL;
		}

		return ItemUseResult::NONE;
	}
}
