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
use watermossmc\world\particle\PotionSplashParticle;
use watermossmc\world\sound\PotionSplashSound;

use function mt_rand;

class ExperienceBottle extends Throwable
{
	public static function getNetworkTypeId() : string
	{
		return EntityIds::XP_BOTTLE;
	}

	protected function getInitialGravity() : float
	{
		return 0.07;
	}

	public function getResultDamage() : int
	{
		return -1;
	}

	public function onHit(ProjectileHitEvent $event) : void
	{
		$this->getWorld()->addParticle($this->location, new PotionSplashParticle(PotionSplashParticle::DEFAULT_COLOR()));
		$this->broadcastSound(new PotionSplashSound());

		$this->getWorld()->dropExperience($this->location, mt_rand(3, 11));
	}
}
