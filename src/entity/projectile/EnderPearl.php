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

use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\event\entity\ProjectileHitEvent;
use watermossmc\network\mcpe\protocol\types\entity\EntityIds;
use watermossmc\world\particle\EndermanTeleportParticle;
use watermossmc\world\sound\EndermanTeleportSound;

class EnderPearl extends Throwable
{
	public static function getNetworkTypeId() : string
	{
		return EntityIds::ENDER_PEARL;
	}

	protected function onHit(ProjectileHitEvent $event) : void
	{
		$owner = $this->getOwningEntity();
		if ($owner !== null) {
			//TODO: check end gateways (when they are added)
			//TODO: spawn endermites at origin

			$this->getWorld()->addParticle($origin = $owner->getPosition(), new EndermanTeleportParticle());
			$this->getWorld()->addSound($origin, new EndermanTeleportSound());
			$owner->teleport($target = $event->getRayTraceResult()->getHitVector());
			$this->getWorld()->addSound($target, new EndermanTeleportSound());

			$owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
		}
	}
}
