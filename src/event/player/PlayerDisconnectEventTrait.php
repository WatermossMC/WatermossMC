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

trait PlayerDisconnectEventTrait
{
	/**
	 * Sets the kick reason shown in the server log and on the console.
	 *
	 * This should be a **short, simple, single-line** message.
	 * Do not use long or multi-line messages here - they will spam the log and server console with useless information.
	 */
	public function setDisconnectReason(Translatable|string $disconnectReason) : void
	{
		$this->disconnectReason = $disconnectReason;
	}

	/**
	 * Returns the kick reason shown in the server log and on the console.
	 * When kicked by the /kick command, the default is something like "Kicked by admin.".
	 */
	public function getDisconnectReason() : Translatable|string
	{
		return $this->disconnectReason;
	}

	/**
	 * Sets the message shown on the player's disconnection screen.
	 * This can be as long as you like, and may contain formatting and newlines.
	 * If this is set to null, the kick reason will be used as the disconnect screen message directly.
	 */
	public function setDisconnectScreenMessage(Translatable|string|null $disconnectScreenMessage) : void
	{
		$this->disconnectScreenMessage = $disconnectScreenMessage;
	}

	/**
	 * Returns the message shown on the player's disconnection screen.
	 * When kicked by the /kick command, the default is something like "Kicked by admin.".
	 * If this is null, the kick reason will be used as the disconnect screen message directly.
	 */
	public function getDisconnectScreenMessage() : Translatable|string|null
	{
		return $this->disconnectScreenMessage ?? $this->disconnectReason;
	}
}
