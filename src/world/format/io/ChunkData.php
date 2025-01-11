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

use watermossmc\nbt\tag\CompoundTag;
use watermossmc\world\format\SubChunk;

final class ChunkData
{
	/**
	 * @param SubChunk[]    $subChunks
	 * @param CompoundTag[] $entityNBT
	 * @param CompoundTag[] $tileNBT
	 *
	 * @phpstan-param array<int, SubChunk> $subChunks
	 * @phpstan-param list<CompoundTag> $entityNBT
	 * @phpstan-param list<CompoundTag> $tileNBT
	 */
	public function __construct(
		private array $subChunks,
		private bool $populated,
		private array $entityNBT,
		private array $tileNBT
	) {
	}

	/**
	 * @return SubChunk[]
	 * @phpstan-return array<int, SubChunk>
	 */
	public function getSubChunks() : array
	{
		return $this->subChunks;
	}

	public function isPopulated() : bool
	{
		return $this->populated;
	}

	/**
	 * @return CompoundTag[]
	 * @phpstan-return list<CompoundTag>
	 */
	public function getEntityNBT() : array
	{
		return $this->entityNBT;
	}

	/**
	 * @return CompoundTag[]
	 * @phpstan-return list<CompoundTag>
	 */
	public function getTileNBT() : array
	{
		return $this->tileNBT;
	}
}
