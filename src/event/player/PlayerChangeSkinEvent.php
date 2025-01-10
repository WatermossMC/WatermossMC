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

namespace watermossmc\event\player;

use watermossmc\entity\Skin;
use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\player\Player;

/**
 * Called when a player changes their skin in-game.
 */
class PlayerChangeSkinEvent extends PlayerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Skin $oldSkin,
		private Skin $newSkin
	) {
		$this->player = $player;
	}

	public function getOldSkin() : Skin
	{
		return $this->oldSkin;
	}

	public function getNewSkin() : Skin
	{
		return $this->newSkin;
	}

	public function setNewSkin(Skin $skin) : void
	{
		$this->newSkin = $skin;
	}
}
