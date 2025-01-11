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

namespace watermossmc\event\world;

use watermossmc\world\World;

/**
 * Called when a world's display name is changed.
 */
final class WorldDisplayNameChangeEvent extends WorldEvent
{
	public function __construct(
		World $world,
		private string $oldName,
		private string $newName
	) {
		parent::__construct($world);
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
