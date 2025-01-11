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
 * This exists to force the client to update the spore blossom every tick, which is necessary for it to generate
 * particles.
 */
final class SporeBlossom extends Spawnable
{
	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		//NOOP
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		//NOOP
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		//NOOP
	}
}
