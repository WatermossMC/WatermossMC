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

use watermossmc\block\utils\MobHeadType;
use watermossmc\data\bedrock\MobHeadTypeIdMap;
use watermossmc\data\SavedDataLoadingException;
use watermossmc\nbt\tag\ByteTag;
use watermossmc\nbt\tag\CompoundTag;

/**
 * @deprecated
 * @see \watermossmc\block\MobHead
 */
class MobHead extends Spawnable
{
	private const TAG_SKULL_TYPE = "SkullType"; //TAG_Byte
	private const TAG_ROT = "Rot"; //TAG_Byte
	private const TAG_MOUTH_MOVING = "MouthMoving"; //TAG_Byte
	private const TAG_MOUTH_TICK_COUNT = "MouthTickCount"; //TAG_Int

	private MobHeadType $mobHeadType = MobHeadType::SKELETON;
	private int $rotation = 0;

	public function readSaveData(CompoundTag $nbt) : void
	{
		if (($skullTypeTag = $nbt->getTag(self::TAG_SKULL_TYPE)) instanceof ByteTag) {
			$mobHeadType = MobHeadTypeIdMap::getInstance()->fromId($skullTypeTag->getValue());
			if ($mobHeadType === null) {
				throw new SavedDataLoadingException("Invalid skull type tag value " . $skullTypeTag->getValue());
			}
			$this->mobHeadType = $mobHeadType;
		}
		$rotation = $nbt->getByte(self::TAG_ROT, 0);
		if ($rotation >= 0 && $rotation <= 15) {
			$this->rotation = $rotation;
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_SKULL_TYPE, MobHeadTypeIdMap::getInstance()->toId($this->mobHeadType));
		$nbt->setByte(self::TAG_ROT, $this->rotation);
	}

	public function setMobHeadType(MobHeadType $type) : void
	{
		$this->mobHeadType = $type;
	}

	public function getMobHeadType() : MobHeadType
	{
		return $this->mobHeadType;
	}

	public function getRotation() : int
	{
		return $this->rotation;
	}

	public function setRotation(int $rotation) : void
	{
		$this->rotation = $rotation;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		$nbt->setByte(self::TAG_SKULL_TYPE, MobHeadTypeIdMap::getInstance()->toId($this->mobHeadType));
		$nbt->setByte(self::TAG_ROT, $this->rotation);
	}
}
