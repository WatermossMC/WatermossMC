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
 * Called after a player is sent a chunk as part of their view radius.
 */
final class PlayerPostChunkSendEvent extends PlayerEvent
{
	public function __construct(
		Player $player,
		private int $chunkX,
		private int $chunkZ
	) {
		$this->player = $player;
	}

	public function getChunkX() : int
	{
		return $this->chunkX;
	}

	public function getChunkZ() : int
	{
		return $this->chunkZ;
	}
}
