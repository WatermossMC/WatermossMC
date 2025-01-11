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

namespace watermossmc\event\entity;

use watermossmc\entity\Entity;
use watermossmc\entity\projectile\Projectile;
use watermossmc\math\RayTraceResult;

class ProjectileHitEntityEvent extends ProjectileHitEvent
{
	public function __construct(
		Projectile $entity,
		RayTraceResult $rayTraceResult,
		private Entity $entityHit
	) {
		parent::__construct($entity, $rayTraceResult);
	}

	/**
	 * Returns the Entity struck by the projectile.
	 */
	public function getEntityHit() : Entity
	{
		return $this->entityHit;
	}
}
