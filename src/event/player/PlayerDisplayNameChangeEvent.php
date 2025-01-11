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

class PlayerDisplayNameChangeEvent extends PlayerEvent
{
	public function __construct(
		Player $player,
		private string $oldName,
		private string $newName
	) {
		$this->player = $player;
	}

	public function getOldName() : string
	{
		return $this->oldName;
	}

	public function getNewName() : string
	{
		return $this->newName;
	}
}
