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

namespace watermossmc\block;

use watermossmc\block\utils\AgeableTrait;
use watermossmc\block\utils\BlockEventHelper;

use function mt_rand;

class FrostedIce extends Ice
{
	use AgeableTrait;

	public const MAX_AGE = 3;

	public function onNearbyBlockChange() : void
	{
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, mt_rand(20, 40));
	}

	public function onRandomTick() : void
	{
		$world = $this->position->getWorld();
		if ((!$this->checkAdjacentBlocks(4) || mt_rand(0, 2) === 0) &&
			$world->getHighestAdjacentFullLightAt($this->position->x, $this->position->y, $this->position->z) >= 12 - $this->age) {
			if ($this->tryMelt()) {
				foreach ($this->getAllSides() as $block) {
					if ($block instanceof FrostedIce) {
						$block->tryMelt();
					}
				}
			}
		} else {
			$world->scheduleDelayedBlockUpdate($this->position, mt_rand(20, 40));
		}
	}

	public function onScheduledUpdate() : void
	{
		$this->onRandomTick();
	}

	private function checkAdjacentBlocks(int $requirement) : bool
	{
		$found = 0;
		for ($x = -1; $x <= 1; ++$x) {
			for ($z = -1; $z <= 1; ++$z) {
				if ($x === 0 && $z === 0) {
					continue;
				}
				if (
					$this->position->getWorld()->getBlockAt($this->position->x + $x, $this->position->y, $this->position->z + $z) instanceof FrostedIce &&
					++$found >= $requirement
				) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Updates the age of the ice, destroying it if appropriate.
	 *
	 * @return bool Whether the ice was destroyed.
	 */
	private function tryMelt() : bool
	{
		$world = $this->position->getWorld();
		if ($this->age >= self::MAX_AGE) {
			BlockEventHelper::melt($this, VanillaBlocks::WATER());
			return true;
		}

		$this->age++;
		$world->setBlock($this->position, $this);
		$world->scheduleDelayedBlockUpdate($this->position, mt_rand(20, 40));
		return false;
	}
}
