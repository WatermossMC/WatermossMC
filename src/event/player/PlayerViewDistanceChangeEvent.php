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

/**
 * Called when a player requests a different viewing distance than the current one.
 */
class PlayerViewDistanceChangeEvent extends PlayerEvent
{
	public function __construct(
		Player $player,
		protected int $oldDistance,
		protected int $newDistance
	) {
		$this->player = $player;
	}

	/**
	 * Returns the new view radius, measured in chunks.
	 */
	public function getNewDistance() : int
	{
		return $this->newDistance;
	}

	/**
	 * Returns the old view radius, measured in chunks.
	 * A value of -1 means that the player has just connected and did not have a view distance before this event.
	 */
	public function getOldDistance() : int
	{
		return $this->oldDistance;
	}
}
