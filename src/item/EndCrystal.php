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
use watermossmc\entity\Location;
use watermossmc\entity\object\EndCrystal as EntityEndCrystal;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

use function count;

class EndCrystal extends Item
{
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult
	{
		if ($blockClicked->getTypeId() === BlockTypeIds::OBSIDIAN || $blockClicked->getTypeId() === BlockTypeIds::BEDROCK) {
			$pos = $blockClicked->getPosition();
			$world = $pos->getWorld();
			$bb = AxisAlignedBB::one()
				->offset($pos->getX(), $pos->getY(), $pos->getZ())
				->extend(Facing::UP, 1);
			if (
				count($world->getNearbyEntities($bb)) === 0 &&
				$blockClicked->getSide(Facing::UP)->getTypeId() === BlockTypeIds::AIR &&
				$blockClicked->getSide(Facing::UP, 2)->getTypeId() === BlockTypeIds::AIR
			) {
				$crystal = new EntityEndCrystal(Location::fromObject($pos->add(0.5, 1, 0.5), $world));
				$crystal->spawnToAll();

				$this->pop();
				return ItemUseResult::SUCCESS;
			}
		}
		return ItemUseResult::NONE;
	}
}
