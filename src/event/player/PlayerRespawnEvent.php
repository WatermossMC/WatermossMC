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

use watermossmc\player\Player;
use watermossmc\utils\Utils;
use watermossmc\world\Position;

/**
 * Called when a player is respawned
 */
class PlayerRespawnEvent extends PlayerEvent
{
	public function __construct(
		Player $player,
		protected Position $position
	) {
		$this->player = $player;
	}

	public function getRespawnPosition() : Position
	{
		return $this->position;
	}

	public function setRespawnPosition(Position $position) : void
	{
		if (!$position->isValid()) {
			throw new \InvalidArgumentException("Spawn position must reference a valid and loaded World");
		}
		Utils::checkVector3NotInfOrNaN($position);
		$this->position = $position;
	}
}
