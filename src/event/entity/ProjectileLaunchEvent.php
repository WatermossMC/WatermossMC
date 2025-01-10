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

namespace watermossmc\event\entity;

use watermossmc\entity\projectile\Projectile;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;

/**
 * @phpstan-extends EntityEvent<Projectile>
 */
class ProjectileLaunchEvent extends EntityEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(Projectile $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * @return Projectile
	 */
	public function getEntity()
	{
		return $this->entity;
	}
}
