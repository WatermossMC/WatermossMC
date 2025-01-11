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

namespace watermossmc\block\tile;

use watermossmc\nbt\tag\CompoundTag;

/**
 * @deprecated
 * As per the wiki, this is an old hack to force daylight sensors to get updated every game tick. This is necessary to
 * ensure that the daylight sensor's power output is always up to date with the current world time.
 * It's theoretically possible to implement this without a blockentity, but this is here to ensure that vanilla can
 * understand daylight sensors in worlds created by PM.
 */
class DaylightSensor extends Tile
{
	public function readSaveData(CompoundTag $nbt) : void
	{

	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{

	}
}
