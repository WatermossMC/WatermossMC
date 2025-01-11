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

use watermossmc\entity\projectile\Projectile;
use watermossmc\math\RayTraceResult;

/**
 * @allowHandle
 * @phpstan-extends EntityEvent<Projectile>
 */
abstract class ProjectileHitEvent extends EntityEvent
{
	public function __construct(
		Projectile $entity,
		private RayTraceResult $rayTraceResult
	) {
		$this->entity = $entity;
	}

	/**
	 * @return Projectile
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * Returns a RayTraceResult object containing information such as the exact position struck, the AABB it hit, and
	 * the face of the AABB that it hit.
	 */
	public function getRayTraceResult() : RayTraceResult
	{
		return $this->rayTraceResult;
	}
}
