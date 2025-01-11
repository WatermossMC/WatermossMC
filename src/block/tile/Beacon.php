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

final class Beacon extends Spawnable
{
	private const TAG_PRIMARY = "primary"; //TAG_Int
	private const TAG_SECONDARY = "secondary"; //TAG_Int

	private int $primaryEffect = 0;
	private int $secondaryEffect = 0;

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setInt(self::TAG_PRIMARY, $this->primaryEffect);
		$nbt->setInt(self::TAG_SECONDARY, $this->secondaryEffect);
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		//TODO: PC uses Primary and Secondary (capitalized first letter), we don't read them here because the IDs would be different
		$this->primaryEffect = $nbt->getInt(self::TAG_PRIMARY, 0);
		$this->secondaryEffect = $nbt->getInt(self::TAG_SECONDARY, 0);
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setInt(self::TAG_PRIMARY, $this->primaryEffect);
		$nbt->setInt(self::TAG_SECONDARY, $this->secondaryEffect);
	}

	public function getPrimaryEffect() : int
	{
		return $this->primaryEffect;
	}

	public function setPrimaryEffect(int $primaryEffect) : void
	{
		$this->primaryEffect = $primaryEffect;
	}

	public function getSecondaryEffect() : int
	{
		return $this->secondaryEffect;
	}

	public function setSecondaryEffect(int $secondaryEffect) : void
	{
		$this->secondaryEffect = $secondaryEffect;
	}
}
