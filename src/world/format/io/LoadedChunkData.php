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

/**
 * Encapsulates information returned when loading a chunk. This includes more information than saving a chunk, since the
 * data might have been upgraded or need post-processing.
 */
final class LoadedChunkData
{
	public const FIXER_FLAG_NONE = 0;
	public const FIXER_FLAG_ALL = ~0;

	public function __construct(
		private ChunkData $data,
		private bool $upgraded,
		private int $fixerFlags
	) {
	}

	public function getData() : ChunkData
	{
		return $this->data;
	}

	public function isUpgraded() : bool
	{
		return $this->upgraded;
	}

	public function getFixerFlags() : int
	{
		return $this->fixerFlags;
	}
}
