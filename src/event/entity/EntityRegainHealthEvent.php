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
class EntityRegainHealthEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	public const CAUSE_REGEN = 0;
	public const CAUSE_EATING = 1;
	public const CAUSE_MAGIC = 2;
	public const CAUSE_CUSTOM = 3;
	public const CAUSE_SATURATION = 4;

	public function __construct(
		Entity $entity,
		private float $amount,
		private int $regainReason
	) {
		$this->entity = $entity;
	}

	public function getAmount() : float
	{
		return $this->amount;
	}

	public function setAmount(float $amount) : void
	{
		$this->amount = $amount;
	}

	/**
	 * Returns one of the CAUSE_* constants to indicate why this regeneration occurred.
	 */
	public function getRegainReason() : int
	{
		return $this->regainReason;
	}
}
