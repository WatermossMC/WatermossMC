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

namespace watermossmc\event\block;

use watermossmc\block\tile\BrewingStand;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;

/**
 * Called when a brewing stand consumes a new fuel item.
 */
class BrewingFuelUseEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	private int $fuelTime = 20;

	public function __construct(
		private BrewingStand $brewingStand
	) {
		parent::__construct($brewingStand->getBlock());
	}

	public function getBrewingStand() : BrewingStand
	{
		return $this->brewingStand;
	}

	/**
	 * Returns how many times the fuel can be used for potion brewing before it runs out.
	 */
	public function getFuelTime() : int
	{
		return $this->fuelTime;
	}

	/**
	 * Sets how many times the fuel can be used for potion brewing before it runs out.
	 */
	public function setFuelTime(int $fuelTime) : void
	{
		if ($fuelTime <= 0) {
			throw new \InvalidArgumentException("Fuel time must be positive");
		}
		$this->fuelTime = $fuelTime;
	}
}
