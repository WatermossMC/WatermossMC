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
 * Called when a player attempts to be transferred to another server, e.g. by using /transferserver.
 */
class PlayerTransferEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		protected string $address,
		protected int $port,
		protected Translatable|string $message
	) {
		$this->player = $player;
	}

	/**
	 * Returns the destination server address. This could be an IP or a domain name.
	 */
	public function getAddress() : string
	{
		return $this->address;
	}

	/**
	 * Sets the destination server address.
	 */
	public function setAddress(string $address) : void
	{
		$this->address = $address;
	}

	/**
	 * Returns the destination server port.
	 */
	public function getPort() : int
	{
		return $this->port;
	}

	/**
	 * Sets the destination server port.
	 */
	public function setPort(int $port) : void
	{
		$this->port = $port;
	}

	/**
	 * Returns the disconnect reason shown in the server log and on the console.
	 */
	public function getMessage() : Translatable|string
	{
		return $this->message;
	}

	/**
	 * Sets the disconnect reason shown in the server log and on the console.
	 */
	public function setMessage(Translatable|string $message) : void
	{
		$this->message = $message;
	}
}
