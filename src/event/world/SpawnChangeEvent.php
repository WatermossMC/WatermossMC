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

use watermossmc\world\Position;
use watermossmc\world\World;

/**
 * An event that is called when a world spawn changes.
 * The previous spawn is included
 */
class SpawnChangeEvent extends WorldEvent
{
	public function __construct(
		World $world,
		private Position $previousSpawn
	) {
		parent::__construct($world);
	}

	public function getPreviousSpawn() : Position
	{
		return $this->previousSpawn;
	}
}
