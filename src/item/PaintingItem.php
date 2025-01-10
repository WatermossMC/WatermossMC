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
use watermossmc\entity\Location;
use watermossmc\entity\object\Painting;
use watermossmc\entity\object\PaintingMotive;
use watermossmc\math\Axis;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\sound\PaintingPlaceSound;

use function array_rand;
use function count;

class PaintingItem extends Item
{
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult
	{
		if (Facing::axis($face) === Axis::Y) {
			return ItemUseResult::NONE;
		}

		$motives = [];

		$totalDimension = 0;
		foreach (PaintingMotive::getAll() as $motive) {
			$currentTotalDimension = $motive->getHeight() + $motive->getWidth();
			if ($currentTotalDimension < $totalDimension) {
				continue;
			}

			if (Painting::canFit($player->getWorld(), $blockReplace->getPosition(), $face, true, $motive)) {
				if ($currentTotalDimension > $totalDimension) {
					$totalDimension = $currentTotalDimension;
					/*
					 * This drops all motive possibilities smaller than this
					 * We use the total of height + width to allow equal chance of horizontal/vertical paintings
					 * when there is an L-shape of space available.
					 */
					$motives = [];
				}

				$motives[] = $motive;
			}
		}

		if (count($motives) === 0) { //No space available
			return ItemUseResult::NONE;
		}

		/** @var PaintingMotive $motive */
		$motive = $motives[array_rand($motives)];

		$replacePos = $blockReplace->getPosition();
		$clickedPos = $blockClicked->getPosition();

		$entity = new Painting(Location::fromObject($replacePos, $replacePos->getWorld()), $clickedPos, $face, $motive);
		$this->pop();
		$entity->spawnToAll();

		$player->getWorld()->addSound($replacePos->add(0.5, 0.5, 0.5), new PaintingPlaceSound());
		return ItemUseResult::SUCCESS;
	}
}
