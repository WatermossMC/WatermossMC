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

namespace watermossmc\world;

use watermossmc\math\Vector3;
use watermossmc\world\format\Chunk;

/**
 * This trait implements no-op default methods for chunk listeners.
 * @see ChunkListener
 */
trait ChunkListenerNoOpTrait /* implements ChunkListener*/
{
	public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk) : void
	{
		//NOOP
	}

	public function onChunkLoaded(int $chunkX, int $chunkZ, Chunk $chunk) : void
	{
		//NOOP
	}

	public function onChunkUnloaded(int $chunkX, int $chunkZ, Chunk $chunk) : void
	{
		//NOOP
	}

	public function onChunkPopulated(int $chunkX, int $chunkZ, Chunk $chunk) : void
	{
		//NOOP
	}

	public function onBlockChanged(Vector3 $block) : void
	{
		//NOOP
	}
}
