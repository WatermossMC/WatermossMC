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

namespace watermossmc\world\light;

use pocketmine\world\format\LightArray;
use watermossmc\block\RuntimeBlockStateRegistry;
use watermossmc\scheduler\AsyncTask;
use watermossmc\world\format\Chunk;
use watermossmc\world\format\io\FastChunkSerializer;
use watermossmc\world\SimpleChunkManager;
use watermossmc\world\utils\SubChunkExplorer;
use watermossmc\world\World;

use function igbinary_serialize;
use function igbinary_unserialize;

class LightPopulationTask extends AsyncTask
{
	private const TLS_KEY_COMPLETION_CALLBACK = "onCompletion";

	public string $chunk;

	private string $resultHeightMap;
	private string $resultSkyLightArrays;
	private string $resultBlockLightArrays;

	/**
	 * @phpstan-param \Closure(array<int, LightArray> $blockLight, array<int, LightArray> $skyLight, array<int, int> $heightMap) : void $onCompletion
	 */
	public function __construct(Chunk $chunk, \Closure $onCompletion)
	{
		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);
		$this->storeLocal(self::TLS_KEY_COMPLETION_CALLBACK, $onCompletion);
	}

	public function onRun() : void
	{
		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);

		$manager = new SimpleChunkManager(World::Y_MIN, World::Y_MAX);
		$manager->setChunk(0, 0, $chunk);

		$blockFactory = RuntimeBlockStateRegistry::getInstance();
		foreach ([
			"Block" => new BlockLightUpdate(new SubChunkExplorer($manager), $blockFactory->lightFilter, $blockFactory->light),
			"Sky" => new SkyLightUpdate(new SubChunkExplorer($manager), $blockFactory->lightFilter, $blockFactory->blocksDirectSkyLight),
		] as $name => $update) {
			$update->recalculateChunk(0, 0);
			$update->execute();
		}

		$chunk->setLightPopulated();

		$this->resultHeightMap = igbinary_serialize($chunk->getHeightMapArray());
		$skyLightArrays = [];
		$blockLightArrays = [];
		foreach ($chunk->getSubChunks() as $y => $subChunk) {
			$skyLightArrays[$y] = $subChunk->getBlockSkyLightArray();
			$blockLightArrays[$y] = $subChunk->getBlockLightArray();
		}
		$this->resultSkyLightArrays = igbinary_serialize($skyLightArrays);
		$this->resultBlockLightArrays = igbinary_serialize($blockLightArrays);
	}

	public function onCompletion() : void
	{
		/** @var int[] $heightMapArray */
		$heightMapArray = igbinary_unserialize($this->resultHeightMap);

		/** @var LightArray[] $skyLightArrays */
		$skyLightArrays = igbinary_unserialize($this->resultSkyLightArrays);
		/** @var LightArray[] $blockLightArrays */
		$blockLightArrays = igbinary_unserialize($this->resultBlockLightArrays);

		/**
		 * @var \Closure
		 * @phpstan-var \Closure(array<int, LightArray> $blockLight, array<int, LightArray> $skyLight, array<int, int> $heightMap) : void
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_COMPLETION_CALLBACK);
		$callback($blockLightArrays, $skyLightArrays, $heightMapArray);
	}
}
