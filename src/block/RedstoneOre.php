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
