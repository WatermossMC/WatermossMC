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

namespace watermossmc\block\tile;

use watermossmc\nbt\tag\CompoundTag;

class EnderChest extends Spawnable
{
	protected int $viewerCount = 0;

	public function getViewerCount() : int
	{
		return $this->viewerCount;
	}

	public function setViewerCount(int $viewerCount) : void
	{
		if ($viewerCount < 0) {
			throw new \InvalidArgumentException('Viewer count cannot be negative');
		}
		$this->viewerCount = $viewerCount;
	}

	public function readSaveData(CompoundTag $nbt) : void
	{

	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{

	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{

	}
}
