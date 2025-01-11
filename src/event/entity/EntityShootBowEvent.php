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
use watermossmc\entity\Living;
use watermossmc\entity\projectile\Projectile;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\item\Item;

use function count;

/**
 * @phpstan-extends EntityEvent<Living>
 */
class EntityShootBowEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	private Entity $projectile;

	public function __construct(
		Living $shooter,
		private Item $bow,
		Projectile $projectile,
		private float $force
	) {
		$this->entity = $shooter;
		$this->projectile = $projectile;
	}

	/**
	 * @return Living
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	public function getBow() : Item
	{
		return $this->bow;
	}

	/**
	 * Returns the entity considered as the projectile in this event.
	 *
	 * NOTE: This might not return a Projectile if a plugin modified the target entity.
	 */
	public function getProjectile() : Entity
	{
		return $this->projectile;
	}

	public function setProjectile(Entity $projectile) : void
	{
		if ($projectile !== $this->projectile) {
			if (count($this->projectile->getViewers()) === 0) {
				$this->projectile->close();
			}
			$this->projectile = $projectile;
		}
	}

	public function getForce() : float
	{
		return $this->force;
	}

	public function setForce(float $force) : void
	{
		$this->force = $force;
	}
}
