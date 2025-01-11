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

use watermossmc\block\Note as BlockNote;
use watermossmc\nbt\tag\CompoundTag;

/**
 * @deprecated
 */
class Note extends Tile
{
	private int $pitch = 0;

	public function readSaveData(CompoundTag $nbt) : void
	{
		if (($pitch = $nbt->getByte("note", $this->pitch)) > BlockNote::MIN_PITCH && $pitch <= BlockNote::MAX_PITCH) {
			$this->pitch = $pitch;
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setByte("note", $this->pitch);
	}

	public function getPitch() : int
	{
		return $this->pitch;
	}

	public function setPitch(int $pitch) : void
	{
		if ($pitch < BlockNote::MIN_PITCH || $pitch > BlockNote::MAX_PITCH) {
			throw new \InvalidArgumentException("Pitch must be in range " . BlockNote::MIN_PITCH . " - " . BlockNote::MAX_PITCH);
		}
		$this->pitch = $pitch;
	}
}
