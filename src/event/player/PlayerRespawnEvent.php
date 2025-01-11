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
