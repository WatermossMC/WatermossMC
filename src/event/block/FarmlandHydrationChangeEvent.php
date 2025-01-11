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

use watermossmc\block\Block;
use watermossmc\block\Farmland;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;

/**
 * Called when farmland hydration is updated.
 */
class FarmlandHydrationChangeEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Block $block,
		private int $oldHydration,
		private int $newHydration,
	) {
		parent::__construct($block);
	}

	public function getOldHydration() : int
	{
		return $this->oldHydration;
	}

	public function getNewHydration() : int
	{
		return $this->newHydration;
	}

	public function setNewHydration(int $hydration) : void
	{
		if ($hydration < 0 || $hydration > Farmland::MAX_WETNESS) {
			throw new \InvalidArgumentException("Hydration must be in range 0 ... " . Farmland::MAX_WETNESS);
		}
		$this->newHydration = $hydration;
	}
}
