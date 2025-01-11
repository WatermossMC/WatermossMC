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
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;

/**
 * Called when an entity decides to explode, before the explosion's impact is calculated.
 * This allows changing the force of the explosion and whether it will destroy blocks.
 *
 * @see EntityExplodeEvent
 *
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityPreExplodeEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	private bool $blockBreaking = true;

	public function __construct(
		Entity $entity,
		protected float $radius
	) {
		if ($radius <= 0) {
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		$this->entity = $entity;
	}

	public function getRadius() : float
	{
		return $this->radius;
	}

	public function setRadius(float $radius) : void
	{
		if ($radius <= 0) {
			throw new \InvalidArgumentException("Explosion radius must be positive");
		}
		$this->radius = $radius;
	}

	public function isBlockBreaking() : bool
	{
		return $this->blockBreaking;
	}

	public function setBlockBreaking(bool $affectsBlocks) : void
	{
		$this->blockBreaking = $affectsBlocks;
	}
}
