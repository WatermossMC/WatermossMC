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

namespace watermossmc\data\bedrock\block\upgrade;

use watermossmc\data\bedrock\block\BlockStateData;
use watermossmc\data\bedrock\block\BlockStateDeserializeException;
use watermossmc\nbt\LittleEndianNbtSerializer;
use watermossmc\utils\BinaryDataException;
use watermossmc\utils\BinaryStream;

/**
 * Handles translating legacy 1.12 block ID/meta into modern blockstates.
 */
final class BlockIdMetaUpgrader
{
	/**
	 * @param BlockStateData[][] $mappingTable
	 * @phpstan-param array<string, array<int, BlockStateData>> $mappingTable
	 */
	public function __construct(
		private array $mappingTable,
		private LegacyBlockIdToStringIdMap $legacyNumericIdMap
	) {
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function fromStringIdMeta(string $id, int $meta) : BlockStateData
	{
		return $this->mappingTable[$id][$meta] ??
			$this->mappingTable[$id][0] ??
			throw new BlockStateDeserializeException("Unknown legacy block string ID $id");
	}

	/**
	 * @throws BlockStateDeserializeException
	 */
	public function fromIntIdMeta(int $id, int $meta) : BlockStateData
	{
		$stringId = $this->legacyNumericIdMap->legacyToString($id);
		if ($stringId === null) {
			throw new BlockStateDeserializeException("Unknown legacy block numeric ID $id");
		}
		return $this->fromStringIdMeta($stringId, $meta);
	}

	/**
	 * Adds a mapping of legacy block numeric ID to modern string ID. This is used for upgrading blocks from pre-1.2.13
	 * worlds (PM3). It's also needed for upgrading flower pot contents and falling blocks from PM4 worlds.
	 */
	public function addIntIdToStringIdMapping(int $intId, string $stringId) : void
	{
		$this->legacyNumericIdMap->add($stringId, $intId);
	}

	/**
	 * Adds a mapping of legacy block ID and meta to modern blockstate data. This may be needed for upgrading data from
	 * stored custom blocks from older versions of WatermossMC.
	 */
	public function addIdMetaToStateMapping(string $stringId, int $meta, BlockStateData $stateData) : void
	{
		if (isset($this->mappingTable[$stringId][$meta])) {
			throw new \InvalidArgumentException("A mapping for $stringId:$meta already exists");
		}
		$this->mappingTable[$stringId][$meta] = $stateData;
	}

	public static function loadFromString(string $data, LegacyBlockIdToStringIdMap $idMap, BlockStateUpgrader $blockStateUpgrader) : self
	{
		$mappingTable = [];

		$legacyStateMapReader = new BinaryStream($data);
		$nbtReader = new LittleEndianNbtSerializer();

		$idCount = $legacyStateMapReader->getUnsignedVarInt();
		for ($idIndex = 0; $idIndex < $idCount; $idIndex++) {
			$id = $legacyStateMapReader->get($legacyStateMapReader->getUnsignedVarInt());

			$metaCount = $legacyStateMapReader->getUnsignedVarInt();
			for ($metaIndex = 0; $metaIndex < $metaCount; $metaIndex++) {
				$meta = $legacyStateMapReader->getUnsignedVarInt();

				$offset = $legacyStateMapReader->getOffset();
				$state = $nbtReader->read($legacyStateMapReader->getBuffer(), $offset)->mustGetCompoundTag();
				$legacyStateMapReader->setOffset($offset);
				$mappingTable[$id][$meta] = $blockStateUpgrader->upgrade(BlockStateData::fromNbt($state));
			}
		}
		if (!$legacyStateMapReader->feof()) {
			throw new BinaryDataException("Unexpected trailing data in legacy state map data");
		}

		return new self($mappingTable, $idMap);
	}
}
