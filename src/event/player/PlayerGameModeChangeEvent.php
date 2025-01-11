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

use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\player\GameMode;
use watermossmc\player\Player;

/**
 * Called when a player has its gamemode changed
 */
class PlayerGameModeChangeEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		protected GameMode $newGamemode
	) {
		$this->player = $player;
	}

	public function getNewGamemode() : GameMode
	{
		return $this->newGamemode;
	}
}
