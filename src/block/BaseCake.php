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
