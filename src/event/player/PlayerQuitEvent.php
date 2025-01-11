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

use watermossmc\lang\Translatable;
use watermossmc\player\Player;

/**
 * Called when a player disconnects from the server for any reason.
 *
 * Some possible reasons include:
 * - being kicked by an operator
 * - disconnecting from the game
 * - timeout due to network connectivity issues
 *
 * @see PlayerKickEvent
 */
class PlayerQuitEvent extends PlayerEvent
{
	public function __construct(
		Player $player,
		protected Translatable|string $quitMessage,
		protected Translatable|string $quitReason
	) {
		$this->player = $player;
	}

	/**
	 * Sets the quit message broadcasted to other players.
	 */
	public function setQuitMessage(Translatable|string $quitMessage) : void
	{
		$this->quitMessage = $quitMessage;
	}

	/**
	 * Returns the quit message broadcasted to other players, e.g. "Steve left the game".
	 */
	public function getQuitMessage() : Translatable|string
	{
		return $this->quitMessage;
	}

	/**
	 * Returns the disconnect reason shown in the server log and on the console.
	 */
	public function getQuitReason() : Translatable|string
	{
		return $this->quitReason;
	}
}
