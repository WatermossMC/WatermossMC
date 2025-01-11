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

use watermossmc\block\utils\FortuneDropHelper;
use watermossmc\block\utils\LightableTrait;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

use function mt_rand;

class RedstoneOre extends Opaque
{
	use LightableTrait;

	public function getLightLevel() : int
	{
		return $this->lit ? 9 : 0;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if (!$this->lit) {
			$this->lit = true;
			$this->position->getWorld()->setBlock($this->position, $this); //no return here - this shouldn't prevent block placement
		}
		return false;
	}

	public function onNearbyBlockChange() : void
	{
		if (!$this->lit) {
			$this->lit = true;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}

	public function ticksRandomly() : bool
	{
		return $this->lit;
	}

	public function onRandomTick() : void
	{
		if ($this->lit) {
			$this->lit = false;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaItems::REDSTONE_DUST()->setCount(FortuneDropHelper::discrete($item, 4, 5))
		];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	protected function getXpDropAmount() : int
	{
		return mt_rand(1, 5);
	}
}
