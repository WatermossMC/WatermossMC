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

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\block;

use watermossmc\entity\Entity;
use watermossmc\entity\projectile\Arrow;
use watermossmc\event\entity\EntityCombustByBlockEvent;
use watermossmc\event\entity\EntityDamageByBlockEvent;
use watermossmc\event\entity\EntityDamageEvent;
use watermossmc\item\Item;

abstract class BaseFire extends Flowable
{
	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function canBeReplaced() : bool
	{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		$ev = new EntityDamageByBlockEvent($this, $entity, EntityDamageEvent::CAUSE_FIRE, $this->getFireDamage());
		$entity->attack($ev);

		$ev = new EntityCombustByBlockEvent($this, $entity, 8);
		if ($entity instanceof Arrow) {
			$ev->cancel();
		}
		$ev->call();
		if (!$ev->isCancelled()) {
			$entity->setOnFire($ev->getDuration());
		}
		return true;
	}

	abstract protected function getFireDamage() : int;

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [];
	}
}
