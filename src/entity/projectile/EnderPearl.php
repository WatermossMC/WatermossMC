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
