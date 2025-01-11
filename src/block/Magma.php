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

use watermossmc\entity\Entity;
use watermossmc\entity\Living;
use watermossmc\event\entity\EntityDamageByBlockEvent;
use watermossmc\event\entity\EntityDamageEvent;

class Magma extends Opaque
{
	public function getLightLevel() : int
	{
		return 3;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		if ($entity instanceof Living && !$entity->isSneaking() && $entity->getFrostWalkerLevel() === 0) {
			$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, 1);
			$entity->attack($ev);
		}
		return true;
	}

	public function burnsForever() : bool
	{
		return true;
	}
}
