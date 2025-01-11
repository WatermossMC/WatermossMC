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

use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\entity\Living;
use watermossmc\player\Player;
use watermossmc\world\sound\BottleEmptySound;

class Potion extends Item implements ConsumableItem
{
	private PotionType $potionType = PotionType::WATER;

	protected function describeState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->potionType);
	}

	public function getType() : PotionType
	{
		return $this->potionType;
	}

	/**
	 * @return $this
	 */
	public function setType(PotionType $type) : self
	{
		$this->potionType = $type;
		return $this;
	}

	public function getMaxStackSize() : int
	{
		return 1;
	}

	public function onConsume(Living $consumer) : void
	{
		$consumer->broadcastSound(new BottleEmptySound());
	}

	public function getAdditionalEffects() : array
	{
		//TODO: check CustomPotionEffects NBT
		return $this->potionType->getEffects();
	}

	public function getResidue() : Item
	{
		return VanillaItems::GLASS_BOTTLE();
	}

	public function canStartUsingItem(Player $player) : bool
	{
		return true;
	}
}
