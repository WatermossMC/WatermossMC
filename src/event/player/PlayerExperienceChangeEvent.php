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

namespace watermossmc\event\player;

use watermossmc\entity\Human;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\event\entity\EntityEvent;

/**
 * Called when a player gains or loses XP levels and/or progress.
 * @phpstan-extends EntityEvent<Human>
 */
class PlayerExperienceChangeEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Human $player,
		private int $oldLevel,
		private float $oldProgress,
		private ?int $newLevel,
		private ?float $newProgress
	) {
		$this->entity = $player;
	}

	public function getOldLevel() : int
	{
		return $this->oldLevel;
	}

	public function getOldProgress() : float
	{
		return $this->oldProgress;
	}

	/**
	 * @return int|null null indicates no change
	 */
	public function getNewLevel() : ?int
	{
		return $this->newLevel;
	}

	/**
	 * @return float|null null indicates no change
	 */
	public function getNewProgress() : ?float
	{
		return $this->newProgress;
	}

	public function setNewLevel(?int $newLevel) : void
	{
		$this->newLevel = $newLevel;
	}

	public function setNewProgress(?float $newProgress) : void
	{
		if ($newProgress < 0.0 || $newProgress > 1.0) {
			throw new \InvalidArgumentException("XP progress must be in range 0-1");
		}
		$this->newProgress = $newProgress;
	}
}
