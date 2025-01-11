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

use watermossmc\block\utils\DyeColor;
use watermossmc\data\bedrock\DyeColorIdMap;
use watermossmc\nbt\tag\ByteTag;
use watermossmc\nbt\tag\CompoundTag;

class Bed extends Spawnable
{
	public const TAG_COLOR = "color";

	private DyeColor $color = DyeColor::RED;

	public function getColor() : DyeColor
	{
		return $this->color;
	}

	public function setColor(DyeColor $color) : void
	{
		$this->color = $color;
	}

	public function readSaveData(CompoundTag $nbt) : void
	{
		if (
			($colorTag = $nbt->getTag(self::TAG_COLOR)) instanceof ByteTag &&
			($color = DyeColorIdMap::getInstance()->fromId($colorTag->getValue())) !== null
		) {
			$this->color = $color;
		} else {
			$this->color = DyeColor::RED; //TODO: this should be an error, but we don't have the systems to handle it yet
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_COLOR, DyeColorIdMap::getInstance()->toId($this->color));
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_COLOR, DyeColorIdMap::getInstance()->toId($this->color));
	}
}
