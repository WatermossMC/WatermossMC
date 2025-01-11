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

namespace watermossmc\event\world;

use watermossmc\world\format\Chunk;
use watermossmc\world\World;

/**
 * Chunk-related events
 */
abstract class ChunkEvent extends WorldEvent
{
	public function __construct(
		World $world,
		private int $chunkX,
		private int $chunkZ,
		private Chunk $chunk
	) {
		parent::__construct($world);
	}

	public function getChunk() : Chunk
	{
		return $this->chunk;
	}

	public function getChunkX() : int
	{
		return $this->chunkX;
	}

	public function getChunkZ() : int
	{
		return $this->chunkZ;
	}
}
