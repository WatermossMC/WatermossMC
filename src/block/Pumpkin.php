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

use watermossmc\item\Item;
use watermossmc\item\Shears;
use watermossmc\item\VanillaItems;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

use function in_array;

class Pumpkin extends Opaque
{
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Shears && in_array($face, Facing::HORIZONTAL, true)) {
			$item->applyDamage(1);
			$world = $this->position->getWorld();
			$world->setBlock($this->position, VanillaBlocks::CARVED_PUMPKIN()->setFacing($face));
			$world->dropItem($this->position->add(0.5, 0.5, 0.5), VanillaItems::PUMPKIN_SEEDS()->setCount(1));
			return true;
		}
		return false;
	}
}
