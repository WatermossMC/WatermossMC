<?php

declare(strict_types=1);

namespace watermossmc\block;

use RuntimeException;

final class BlockRuntimeIdConverter
{
	public function __construct(
		private RuntimeIdMap $runtimeIdMap
	) {
	}

	public function toRuntimeId(Block $block): int
	{
		return $this->runtimeIdMap->getRuntimeId(
			$block->getState()
		);
	}

	public function toRuntimeIdFromState(BlockState $state): int
	{
		return $this->runtimeIdMap->getRuntimeId($state);
	}

	public function fromRuntimeId(int $runtimeId): BlockState
	{
		return $this->runtimeIdMap->getState($runtimeId);
	}
}