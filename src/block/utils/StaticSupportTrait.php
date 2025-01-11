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

namespace watermossmc\block\utils;

use watermossmc\block\Block;
use watermossmc\math\Vector3;

/**
 * Used by blocks which always have the same support requirements no matter what state they are in.
 * Prevents placement if support isn't available, and automatically destroys itself if support is removed.
 */
trait StaticSupportTrait
{
	/**
	 * Implement this to define the block's support requirements.
	 */
	abstract private function canBeSupportedAt(Block $block) : bool;

	/**
	 * @see Block::canBePlacedAt()
	 */
	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool
	{
		return $this->canBeSupportedAt($blockReplace) && parent::canBePlacedAt($blockReplace, $clickVector, $face, $isClickedBlock);
	}

	/**
	 * @see Block::onNearbyBlockChange()
	 */
	public function onNearbyBlockChange() : void
	{
		if (!$this->canBeSupportedAt($this)) {
			$this->position->getWorld()->useBreakOn($this->position);
		} else {
			parent::onNearbyBlockChange();
		}
	}
}
