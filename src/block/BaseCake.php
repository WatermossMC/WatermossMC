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

namespace watermossmc\block;

use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\entity\effect\EffectInstance;
use watermossmc\entity\FoodSource;
use watermossmc\entity\Living;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

abstract class BaseCake extends Transparent implements FoodSource
{
	use StaticSupportTrait;

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getSide(Facing::DOWN)->getTypeId() !== BlockTypeIds::AIR;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($player !== null) {
			return $player->consumeObject($this);
		}

		return false;
	}

	public function getFoodRestore() : int
	{
		return 2;
	}

	public function getSaturationRestore() : float
	{
		return 0.4;
	}

	public function requiresHunger() : bool
	{
		return true;
	}

	/**
	 * @return EffectInstance[]
	 */
	public function getAdditionalEffects() : array
	{
		return [];
	}

	abstract public function getResidue() : Block;

	public function onConsume(Living $consumer) : void
	{
		$this->position->getWorld()->setBlock($this->position, $this->getResidue());
	}
}
