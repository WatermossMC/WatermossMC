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

namespace watermossmc\world\format\io\region;

use watermossmc\block\Block;
use watermossmc\data\bedrock\BiomeIds;
use watermossmc\nbt\BigEndianNbtSerializer;
use watermossmc\nbt\NbtDataException;
use watermossmc\nbt\tag\ByteArrayTag;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\IntArrayTag;
use watermossmc\nbt\tag\ListTag;
use watermossmc\world\format\Chunk;
use watermossmc\world\format\io\ChunkData;
use watermossmc\world\format\io\ChunkUtils;
use watermossmc\world\format\io\exception\CorruptedChunkException;
use watermossmc\world\format\io\LoadedChunkData;
use watermossmc\world\format\PalettedBlockArray;
use watermossmc\world\format\SubChunk;

use function strlen;
use function zlib_decode;

/**
 * Trait containing I/O methods for handling legacy Anvil-style chunks.
 *
 * Motivation: In the future PMAnvil will become a legacy read-only format, but Anvil will continue to exist for the sake
 * of handling worlds in the PC 1.13 format. Thus, we don't want PMAnvil getting accidentally influenced by changes
 * happening to the underlying Anvil, because it only uses the legacy part.
 *
 * @internal
 */
trait LegacyAnvilChunkTrait
{
	/**
	 * @throws CorruptedChunkException
	 */
	protected function deserializeChunk(string $data, \Logger $logger) : ?LoadedChunkData
	{
		$decompressed = @zlib_decode($data);
		if ($decompressed === false) {
			throw new CorruptedChunkException("Failed to decompress chunk NBT");
		}
		$nbt = new BigEndianNbtSerializer();
		try {
			$chunk = $nbt->read($decompressed)->mustGetCompoundTag();
		} catch (NbtDataException $e) {
			throw new CorruptedChunkException($e->getMessage(), 0, $e);
		}
		$chunk = $chunk->getTag("Level");
		if (!($chunk instanceof CompoundTag)) {
			throw new CorruptedChunkException("'Level' key is missing from chunk NBT");
		}

		$makeBiomeArray = function (string $biomeIds) : PalettedBlockArray {
			if (strlen($biomeIds) !== 256) {
				throw new CorruptedChunkException("Expected biome array to be exactly 256 bytes, got " . strlen($biomeIds));
			}
			//TODO: we may need to convert legacy biome IDs
			return ChunkUtils::extrapolate3DBiomes($biomeIds);
		};

		if (($biomeColorsTag = $chunk->getTag("BiomeColors")) instanceof IntArrayTag) {
			$biomes3d = $makeBiomeArray(ChunkUtils::convertBiomeColors($biomeColorsTag->getValue())); //Convert back to original format
		} elseif (($biomesTag = $chunk->getTag("Biomes")) instanceof ByteArrayTag) {
			$biomes3d = $makeBiomeArray($biomesTag->getValue());
		} else {
			$biomes3d = new PalettedBlockArray(BiomeIds::OCEAN);
		}

		$subChunks = [];
		$subChunksTag = $chunk->getListTag("Sections") ?? [];
		foreach ($subChunksTag as $subChunk) {
			if ($subChunk instanceof CompoundTag) {
				$y = $subChunk->getByte("Y");
				$subChunks[$y] = $this->deserializeSubChunk($subChunk, clone $biomes3d, new \PrefixedLogger($logger, "Subchunk y=$y"));
			}
		}
		for ($y = Chunk::MIN_SUBCHUNK_INDEX; $y <= Chunk::MAX_SUBCHUNK_INDEX; ++$y) {
			if (!isset($subChunks[$y])) {
				$subChunks[$y] = new SubChunk(Block::EMPTY_STATE_ID, [], clone $biomes3d);
			}
		}

		return new LoadedChunkData(
			data: new ChunkData(
				$subChunks,
				$chunk->getByte("TerrainPopulated", 0) !== 0,
				($entitiesTag = $chunk->getTag("Entities")) instanceof ListTag ? self::getCompoundList("Entities", $entitiesTag) : [],
				($tilesTag = $chunk->getTag("TileEntities")) instanceof ListTag ? self::getCompoundList("TileEntities", $tilesTag) : [],
			),
			upgraded: true,
			fixerFlags: LoadedChunkData::FIXER_FLAG_ALL
		);
	}

	abstract protected function deserializeSubChunk(CompoundTag $subChunk, PalettedBlockArray $biomes3d, \Logger $logger) : SubChunk;

}
