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
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityCombustEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	protected int $duration;

	public function __construct(Entity $combustee, int $duration)
	{
		$this->entity = $combustee;
		$this->duration = $duration;
	}

	/**
	 * Returns the duration in seconds the entity will burn for.
	 */
	public function getDuration() : int
	{
		return $this->duration;
	}

	public function setDuration(int $duration) : void
	{
		$this->duration = $duration;
	}
}
