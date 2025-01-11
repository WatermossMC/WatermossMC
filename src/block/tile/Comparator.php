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

use watermossmc\block\RedstoneComparator;
use watermossmc\nbt\tag\CompoundTag;

/**
 * @deprecated
 * @see RedstoneComparator
 */
class Comparator extends Tile
{
	private const TAG_OUTPUT_SIGNAL = "OutputSignal"; //int

	protected int $signalStrength = 0;

	public function getSignalStrength() : int
	{
		return $this->signalStrength;
	}

	public function setSignalStrength(int $signalStrength) : void
	{
		$this->signalStrength = $signalStrength;
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		$this->signalStrength = $nbt->getInt(self::TAG_OUTPUT_SIGNAL, 0);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setInt(self::TAG_OUTPUT_SIGNAL, $this->signalStrength);
	}
}
