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

use watermossmc\block\Block;
use watermossmc\nbt\tag\ByteTag;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\IntTag;
use watermossmc\nbt\tag\StringTag;
use watermossmc\network\mcpe\protocol\types\CacheableNbt;

use function get_class;

abstract class Spawnable extends Tile
{
	/** @phpstan-var CacheableNbt<CompoundTag>|null */
	private ?CacheableNbt $spawnCompoundCache = null;

	/**
	 * @deprecated
	 */
	public function isDirty() : bool
	{
		return $this->spawnCompoundCache === null;
	}

	/**
	 * @deprecated
	 */
	public function setDirty(bool $dirty = true) : void
	{
		$this->clearSpawnCompoundCache();
	}

	public function clearSpawnCompoundCache() : void
	{
		$this->spawnCompoundCache = null;
	}

	/**
	 * The Bedrock client won't re-render a block if the block's state properties didn't change. This is a problem when
	 * the tile may affect the block's appearance. For example, a cauldron's liquid changes colour based on the dye
	 * inside.
	 *
	 * This is worked around in vanilla by modifying one of the block's state properties to a different value, and then
	 * changing it back again. Since we don't want to litter core implementation with hacks like this, we brush it under
	 * the rug into Tile.
	 *
	 * @return ByteTag[]|IntTag[]|StringTag[]
	 * @phpstan-return array<string, IntTag|StringTag|ByteTag>
	 */
	public function getRenderUpdateBugWorkaroundStateProperties(Block $block) : array
	{
		return [];
	}

	/**
	 * Returns encoded NBT (varint, little-endian) used to spawn this tile to clients. Uses cache where possible,
	 * populates cache if it is null.
	 *
	 * @phpstan-return CacheableNbt<CompoundTag>
	 */
	final public function getSerializedSpawnCompound() : CacheableNbt
	{
		if ($this->spawnCompoundCache === null) {
			$this->spawnCompoundCache = new CacheableNbt($this->getSpawnCompound());
		}

		return $this->spawnCompoundCache;
	}

	final public function getSpawnCompound() : CompoundTag
	{
		$nbt = CompoundTag::create()
			->setString(self::TAG_ID, TileFactory::getInstance()->getSaveId(get_class($this))) //TODO: disassociate network ID from save ID
			->setInt(self::TAG_X, $this->position->x)
			->setInt(self::TAG_Y, $this->position->y)
			->setInt(self::TAG_Z, $this->position->z);
		$this->addAdditionalSpawnData($nbt);
		return $nbt;
	}

	/**
	 * An extension to getSpawnCompound() for
	 * further modifying the generic tile NBT.
	 */
	abstract protected function addAdditionalSpawnData(CompoundTag $nbt) : void;
}
