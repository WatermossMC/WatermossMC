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
