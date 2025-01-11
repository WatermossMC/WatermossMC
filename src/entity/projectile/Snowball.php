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

namespace watermossmc\entity\projectile;

use watermossmc\event\entity\ProjectileHitEvent;
use watermossmc\network\mcpe\protocol\types\entity\EntityIds;
use watermossmc\world\particle\SnowballPoofParticle;

class Snowball extends Throwable
{
	public static function getNetworkTypeId() : string
	{
		return EntityIds::SNOWBALL;
	}

	protected function onHit(ProjectileHitEvent $event) : void
	{
		$world = $this->getWorld();
		for ($i = 0; $i < 6; ++$i) {
			$world->addParticle($this->location, new SnowballPoofParticle());
		}
	}
}
