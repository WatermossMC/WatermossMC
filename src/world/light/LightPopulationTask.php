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
	private string $resultSky\pocketmine\worldormat\LightArrays;
	private string $resultBlock\pocketmine\worldormat\LightArrays;

	/**
	 * @phpstan-param \Closure(array<int, \pocketmine\worldormat\LightArray> $blockLight, array<int, \pocketmine\worldormat\LightArray> $skyLight, array<int, int> $heightMap) : void $onCompletion
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
		$sky\pocketmine\worldormat\LightArrays = [];
		$block\pocketmine\worldormat\LightArrays = [];
		foreach ($chunk->getSubChunks() as $y => $subChunk) {
			$sky\pocketmine\worldormat\LightArrays[$y] = $subChunk->getBlockSky\pocketmine\worldormat\LightArray();
			$block\pocketmine\worldormat\LightArrays[$y] = $subChunk->getBlock\pocketmine\worldormat\LightArray();
		}
		$this->resultSky\pocketmine\worldormat\LightArrays = igbinary_serialize($sky\pocketmine\worldormat\LightArrays);
		$this->resultBlock\pocketmine\worldormat\LightArrays = igbinary_serialize($block\pocketmine\worldormat\LightArrays);
	}

	public function onCompletion() : void
	{
		/** @var int[] $heightMapArray */
		$heightMapArray = igbinary_unserialize($this->resultHeightMap);

		/** @var \pocketmine\worldormat\LightArray[] $sky\pocketmine\worldormat\LightArrays */
		$sky\pocketmine\worldormat\LightArrays = igbinary_unserialize($this->resultSky\pocketmine\worldormat\LightArrays);
		/** @var \pocketmine\worldormat\LightArray[] $block\pocketmine\worldormat\LightArrays */
		$block\pocketmine\worldormat\LightArrays = igbinary_unserialize($this->resultBlock\pocketmine\worldormat\LightArrays);

		/**
		 * @var \Closure
		 * @phpstan-var \Closure(array<int, \pocketmine\worldormat\LightArray> $blockLight, array<int, \pocketmine\worldormat\LightArray> $skyLight, array<int, int> $heightMap) : void
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_COMPLETION_CALLBACK);
		$callback($block\pocketmine\worldormat\LightArrays, $sky\pocketmine\worldormat\LightArrays, $heightMapArray);
	}
}
