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

use watermossmc\block\utils\AmethystTrait;
use watermossmc\block\utils\BlockEventHelper;
use watermossmc\item\Item;
use watermossmc\math\Facing;

use function array_rand;
use function mt_rand;

final class BuddingAmethyst extends Opaque
{
	use AmethystTrait;

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		if (mt_rand(1, 5) === 1) {
			$face = Facing::ALL[array_rand(Facing::ALL)];

			$adjacent = $this->getSide($face);
			//TODO: amethyst buds can spawn in water - we need waterlogging support for this

			$newStage = null;

			if ($adjacent->getTypeId() === BlockTypeIds::AIR) {
				$newStage = AmethystCluster::STAGE_SMALL_BUD;
			} elseif (
				$adjacent->getTypeId() === BlockTypeIds::AMETHYST_CLUSTER &&
				$adjacent instanceof AmethystCluster &&
				$adjacent->getStage() < AmethystCluster::STAGE_CLUSTER &&
				$adjacent->getFacing() === $face
			) {
				$newStage = $adjacent->getStage() + 1;
			}
			if ($newStage !== null) {
				BlockEventHelper::grow($adjacent, VanillaBlocks::AMETHYST_CLUSTER()->setStage($newStage)->setFacing($face), null);
			}
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}
}
