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

use watermossmc\entity\Location;
use watermossmc\entity\projectile\Egg as EggEntity;
use watermossmc\entity\projectile\Throwable;
use watermossmc\player\Player;

class Egg extends ProjectileItem
{
	public function getMaxStackSize() : int
	{
		return 16;
	}

	protected function createEntity(Location $location, Player $thrower) : Throwable
	{
		return new EggEntity($location, $thrower);
	}

	public function getThrowForce() : float
	{
		return 1.5;
	}
}
