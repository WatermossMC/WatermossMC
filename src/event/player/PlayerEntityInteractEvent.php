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

namespace watermossmc\event\player;

use watermossmc\entity\Entity;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\math\Vector3;
use watermossmc\player\Player;

/**
 * Called when a player interacts with an entity (e.g. shearing a sheep, naming a mob using a nametag).
 */
class PlayerEntityInteractEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Entity $entity,
		private Vector3 $clickPos
	) {
		$this->player = $player;
	}

	public function getEntity() : Entity
	{
		return $this->entity;
	}

	/**
	 * Returns the absolute coordinates of the click. This is usually on the surface of the entity's hitbox.
	 */
	public function getClickPosition() : Vector3
	{
		return $this->clickPos;
	}
}
