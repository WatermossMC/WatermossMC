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
use watermossmc\entity\Location;
use watermossmc\entity\projectile\SplashPotion as SplashPotionEntity;
use watermossmc\entity\projectile\Throwable;
use watermossmc\player\Player;

class SplashPotion extends ProjectileItem
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

	protected function createEntity(Location $location, Player $thrower) : Throwable
	{
		return new SplashPotionEntity($location, $thrower, $this->potionType);
	}

	public function getThrowForce() : float
	{
		return 0.5;
	}
}
