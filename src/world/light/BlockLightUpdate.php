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
use watermossmc\world\format\SubChunk;
use watermossmc\world\utils\SubChunkExplorer;
use watermossmc\world\utils\SubChunkExplorerStatus;

use function max;

class BlockLightUpdate extends LightUpdate
{
	/**
	 * @param int[] $lightFilters
	 * @param int[] $lightEmitters
	 * @phpstan-param array<int, int> $lightFilters
	 * @phpstan-param array<int, int> $lightEmitters
	 */
	public function __construct(
		SubChunkExplorer $subChunkExplorer,
		array $lightFilters,
		private array $lightEmitters
	) {
		parent::__construct($subChunkExplorer, $lightFilters);
	}

	protected function getCurrentLightArray() : LightArray
	{
		return $this->subChunkExplorer->currentSubChunk->getBlockLightArray();
	}

	public function recalculateNode(int $x, int $y, int $z) : void
	{
		if ($this->subChunkExplorer->moveTo($x, $y, $z) !== SubChunkExplorerStatus::INVALID) {
			$block = $this->subChunkExplorer->currentSubChunk->getBlockStateId($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK);
			$this->setAndUpdateLight($x, $y, $z, max($this->lightEmitters[$block] ?? 0, $this->getHighestAdjacentLight($x, $y, $z) - ($this->lightFilters[$block] ?? self::BASE_LIGHT_FILTER)));
		}
	}

	public function recalculateChunk(int $chunkX, int $chunkZ) : int
	{
		if ($this->subChunkExplorer->moveToChunk($chunkX, 0, $chunkZ) === SubChunkExplorerStatus::INVALID) {
			throw new \InvalidArgumentException("Chunk $chunkX $chunkZ does not exist");
		}
		$chunk = $this->subChunkExplorer->currentChunk;

		$lightSources = 0;
		foreach ($chunk->getSubChunks() as $subChunkY => $subChunk) {
			$subChunk->setBlockLightArray(LightArray::fill(0));

			foreach ($subChunk->getBlockLayers() as $layer) {
				foreach ($layer->getPalette() as $state) {
					if (($this->lightEmitters[$state] ?? 0) > 0) {
						$lightSources += $this->scanForLightEmittingBlocks($subChunk, $chunkX << SubChunk::COORD_BIT_SIZE, $subChunkY << SubChunk::COORD_BIT_SIZE, $chunkZ << SubChunk::COORD_BIT_SIZE);
						break 2;
					}
				}
			}
		}

		return $lightSources;
	}

	private function scanForLightEmittingBlocks(SubChunk $subChunk, int $baseX, int $baseY, int $baseZ) : int
	{
		$lightSources = 0;
		for ($x = 0; $x < SubChunk::EDGE_LENGTH; ++$x) {
			for ($z = 0; $z < SubChunk::EDGE_LENGTH; ++$z) {
				for ($y = 0; $y < SubChunk::EDGE_LENGTH; ++$y) {
					$light = $this->lightEmitters[$subChunk->getBlockStateId($x, $y, $z)] ?? 0;
					if ($light > 0) {
						$this->setAndUpdateLight(
							$baseX + $x,
							$baseY + $y,
							$baseZ + $z,
							$light
						);
						$lightSources++;
					}
				}
			}
		}
		return $lightSources;
	}
}
