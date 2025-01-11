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

namespace watermossmc\data\bedrock\item;

use watermossmc\data\bedrock\block\BlockStateData;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\VersionInfo;

final class SavedItemData
{
	public const TAG_NAME = "Name";
	public const TAG_DAMAGE = "Damage";
	public const TAG_BLOCK = "Block";
	public const TAG_TAG = "tag";

	public function __construct(
		private string $name,
		private int $meta = 0,
		private ?BlockStateData $block = null,
		private ?CompoundTag $tag = null
	) {
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getMeta() : int
	{
		return $this->meta;
	}

	public function getBlock() : ?BlockStateData
	{
		return $this->block;
	}

	public function getTag() : ?CompoundTag
	{
		return $this->tag;
	}

	public function toNbt() : CompoundTag
	{
		$result = CompoundTag::create();
		$result->setString(self::TAG_NAME, $this->name);
		$result->setShort(self::TAG_DAMAGE, $this->meta);

		if ($this->block !== null) {
			$result->setTag(self::TAG_BLOCK, $this->block->toNbt());
		}
		if ($this->tag !== null) {
			$result->setTag(self::TAG_TAG, $this->tag);
		}
		$result->setLong(VersionInfo::TAG_WORLD_DATA_VERSION, VersionInfo::WORLD_DATA_VERSION);

		return $result;
	}
}
