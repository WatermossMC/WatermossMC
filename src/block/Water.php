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
