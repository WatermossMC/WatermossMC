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

namespace watermossmc\world\utils;

use watermossmc\world\ChunkManager;
use watermossmc\world\format\Chunk;
use watermossmc\world\format\SubChunk;

class SubChunkExplorer
{
	public ?Chunk $currentChunk = null;
	public ?SubChunk $currentSubChunk = null;

	protected int $currentX;
	protected int $currentY;
	protected int $currentZ;

	public function __construct(
		protected ChunkManager $world
	) {
	}

	/**
	 * @phpstan-return SubChunkExplorerStatus::*
	 */
	public function moveTo(int $x, int $y, int $z) : int
	{
		$newChunkX = $x >> SubChunk::COORD_BIT_SIZE;
		$newChunkZ = $z >> SubChunk::COORD_BIT_SIZE;
		if ($this->currentChunk === null || $this->currentX !== $newChunkX || $this->currentZ !== $newChunkZ) {
			$this->currentX = $newChunkX;
			$this->currentZ = $newChunkZ;
			$this->currentSubChunk = null;

			$this->currentChunk = $this->world->getChunk($this->currentX, $this->currentZ);
			if ($this->currentChunk === null) {
				return SubChunkExplorerStatus::INVALID;
			}
		}

		$newChunkY = $y >> SubChunk::COORD_BIT_SIZE;
		if ($this->currentSubChunk === null || $this->currentY !== $newChunkY) {
			$this->currentY = $newChunkY;

			if ($this->currentY < Chunk::MIN_SUBCHUNK_INDEX || $this->currentY > Chunk::MAX_SUBCHUNK_INDEX) {
				$this->currentSubChunk = null;
				return SubChunkExplorerStatus::INVALID;
			}

			$this->currentSubChunk = $this->currentChunk->getSubChunk($newChunkY);
			return SubChunkExplorerStatus::MOVED;
		}

		return SubChunkExplorerStatus::OK;
	}

	/**
	 * @phpstan-return SubChunkExplorerStatus::*
	 */
	public function moveToChunk(int $chunkX, int $chunkY, int $chunkZ) : int
	{
		//this is a cold path, so we don't care much if it's a bit slower (extra fcall overhead)
		return $this->moveTo($chunkX << SubChunk::COORD_BIT_SIZE, $chunkY << SubChunk::COORD_BIT_SIZE, $chunkZ << SubChunk::COORD_BIT_SIZE);
	}

	/**
	 * Returns whether we currently have a valid terrain pointer.
	 */
	public function isValid() : bool
	{
		return $this->currentSubChunk !== null;
	}

	public function invalidate() : void
	{
		$this->currentChunk = null;
		$this->currentSubChunk = null;
	}
}
