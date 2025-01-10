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

use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\event\Event;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\player\Player;

/**
 * Called when a player's data is about to be saved to disk.
 */
class PlayerDataSaveEvent extends Event implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		protected CompoundTag $data,
		protected string $playerName,
		private ?Player $player
	) {
	}

	/**
	 * Returns the data to be written to disk as a CompoundTag
	 */
	public function getSaveData() : CompoundTag
	{
		return $this->data;
	}

	public function setSaveData(CompoundTag $data) : void
	{
		$this->data = $data;
	}

	/**
	 * Returns the username of the player whose data is being saved. This is not necessarily an online player.
	 */
	public function getPlayerName() : string
	{
		return $this->playerName;
	}

	/**
	 * Returns the player whose data is being saved, if online.
	 * If null, this data is for an offline player (possibly just disconnected).
	 */
	public function getPlayer() : ?Player
	{
		return $this->player;
	}
}
