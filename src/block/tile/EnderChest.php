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
