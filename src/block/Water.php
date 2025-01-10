<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

namespace watermossmc\block;

use watermossmc\entity\Entity;
use watermossmc\world\sound\BucketEmptyWaterSound;
use watermossmc\world\sound\BucketFillWaterSound;
use watermossmc\world\sound\Sound;

class Water extends Liquid
{
	public function getLightFilter() : int
	{
		return 2;
	}

	public function getBucketFillSound() : Sound
	{
		return new BucketFillWaterSound();
	}

	public function getBucketEmptySound() : Sound
	{
		return new BucketEmptyWaterSound();
	}

	public function tickRate() : int
	{
		return 5;
	}

	public function getMinAdjacentSourcesToFormSource() : ?int
	{
		return 2;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		$entity->resetFallDistance();
		if ($entity->isOnFire()) {
			$entity->extinguish();
		}
		return true;
	}
}
