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

namespace watermossmc\player;

use watermossmc\nbt\tag\CompoundTag;

/**
 * Handles storage of player data. Implementations must treat player names in a case-insensitive manner.
 */
interface PlayerDataProvider
{
	/**
	 * Returns whether there are any data associated with the given player name.
	 */
	public function hasData(string $name) : bool;

	/**
	 * Returns the data associated with the given player name, or null if there is no data.
	 * TODO: we need an async version of this
	 *
	 * @throws PlayerDataLoadException
	 */
	public function loadData(string $name) : ?CompoundTag;

	/**
	 * Saves data for the give player name.
	 *
	 * @throws PlayerDataSaveException
	 */
	public function saveData(string $name, CompoundTag $data) : void;
}
