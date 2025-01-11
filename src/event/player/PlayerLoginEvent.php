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
use watermossmc\lang\Translatable;
use watermossmc\player\Player;

/**
 * Called after the player has successfully authenticated, before it spawns. The player is on the loading screen when
 * this is called.
 * Cancelling this event will cause the player to be disconnected with the kick message set.
 */
class PlayerLoginEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		protected Translatable|string $kickMessage
	) {
		$this->player = $player;
	}

	public function setKickMessage(Translatable|string $kickMessage) : void
	{
		$this->kickMessage = $kickMessage;
	}

	public function getKickMessage() : Translatable|string
	{
		return $this->kickMessage;
	}
}
