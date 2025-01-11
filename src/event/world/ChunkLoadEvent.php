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
 * Called when a Chunk is loaded or newly created by the world generator.
 */
class ChunkLoadEvent extends ChunkEvent
{
	public function __construct(
		World $world,
		int $chunkX,
		int $chunkZ,
		Chunk $chunk,
		private bool $newChunk
	) {
		parent::__construct($world, $chunkX, $chunkZ, $chunk);
	}

	/**
	 * Returns whether the chunk is newly generated.
	 * If false, the chunk was loaded from storage.
	 */
	public function isNewChunk() : bool
	{
		return $this->newChunk;
	}
}
