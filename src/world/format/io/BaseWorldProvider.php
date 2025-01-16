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

namespace watermossmc\world\format\io;

use watermossmc\data\bedrock\block\BlockStateDeserializeException;
use watermossmc\data\bedrock\block\BlockStateDeserializer;
use watermossmc\data\bedrock\block\BlockStateSerializer;
use watermossmc\data\bedrock\block\upgrade\BlockDataUpgrader;
use watermossmc\world\format\io\exception\CorruptedWorldException;
use watermossmc\world\format\io\exception\UnsupportedWorldFormatException;

use watermossmc\world\WorldException;

use function count;
use function file_exists;
use function implode;

abstract class BaseWorldProvider implements WorldProvider
{
	protected WorldData $worldData;

	protected BlockStateDeserializer $blockStateDeserializer;
	protected BlockDataUpgrader $blockDataUpgrader;
	protected BlockStateSerializer $blockStateSerializer;

	public function __construct(
		protected string $path,
		protected \Logger $logger
	) {
		if (!file_exists($path)) {
			throw new WorldException("World does not exist");
		}

		//TODO: this should not rely on singletons
		$this->blockStateDeserializer = GlobalBlockStateHandlers::getDeserializer();
		$this->blockDataUpgrader = GlobalBlockStateHandlers::getUpgrader();
		$this->blockStateSerializer = GlobalBlockStateHandlers::getSerializer();

		$this->worldData = $this->loadLevelData();
	}

	/**
	 * @throws CorruptedWorldException
	 * @throws UnsupportedWorldFormatException
	 */
	abstract protected function loadLevelData() : WorldData;

	private function translatePalette(PalettedBlockArray $blockArray, \Logger $logger) : PalettedBlockArray
	{
		//TODO: missing type info in stubs
		/** @phpstan-var list<int> $palette */
		$palette = $blockArray->getPalette();

		$newPalette = [];
		$blockDecodeErrors = [];
		foreach ($palette as $k => $legacyIdMeta) {
			//TODO: remember data for unknown states so we can implement them later
			$id = $legacyIdMeta >> 4;
			$meta = $legacyIdMeta & 0xf;
			try {
				$newStateData = $this->blockDataUpgrader->upgradeIntIdMeta($id, $meta);
			} catch (BlockStateDeserializeException $e) {
				$blockDecodeErrors[] = "Palette offset $k / Failed to upgrade legacy ID/meta $id:$meta: " . $e->getMessage();
				$newStateData = GlobalBlockStateHandlers::getUnknownBlockStateData();
			}

			try {
				$newPalette[$k] = $this->blockStateDeserializer->deserialize($newStateData);
			} catch (BlockStateDeserializeException $e) {
				//this should never happen anyway - if the upgrader returned an invalid state, we have bigger problems
				$blockDecodeErrors[] = "Palette offset $k / Failed to deserialize upgraded state $id:$meta: " . $e->getMessage();
				$newPalette[$k] = $this->blockStateDeserializer->deserialize(GlobalBlockStateHandlers::getUnknownBlockStateData());
			}
		}

		if (count($blockDecodeErrors) > 0) {
			$logger->error("Errors decoding/upgrading blocks:\n - " . implode("\n - ", $blockDecodeErrors));
		}

		//TODO: this is sub-optimal since it reallocates the offset table multiple times
		return PalettedBlockArray::fromData(
			$blockArray->getBitsPerBlock(),
			$blockArray->getWordArray(),
			$newPalette
		);
	}

	protected function palettizeLegacySubChunkXZY(string $idArray, string $metaArray, \Logger $logger) : PalettedBlockArray
	{
		return $this->translatePalette(\pocketmine\worldormat\io\SubChunkConverter::convertSubChunkXZY($idArray, $metaArray), $logger);
	}

	protected function palettizeLegacySubChunkYZX(string $idArray, string $metaArray, \Logger $logger) : PalettedBlockArray
	{
		return $this->translatePalette(\pocketmine\worldormat\io\SubChunkConverter::convertSubChunkYZX($idArray, $metaArray), $logger);
	}

	protected function palettizeLegacySubChunkFromColumn(string $idArray, string $metaArray, int $yOffset, \Logger $logger) : PalettedBlockArray
	{
		return $this->translatePalette(\pocketmine\worldormat\io\SubChunkConverter::convertSubChunkFromLegacyColumn($idArray, $metaArray, $yOffset), $logger);
	}

	public function getPath() : string
	{
		return $this->path;
	}

	public function getWorldData() : WorldData
	{
		return $this->worldData;
	}
}
