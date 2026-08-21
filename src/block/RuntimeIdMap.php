<?php

declare(strict_types=1);

namespace watermossmc\block;

use RuntimeException;

final class RuntimeIdMap
{
	/** @var array<string, int> */
	private array $stateToRuntimeId = [];

	/** @var array<int, BlockState> */
	private array $runtimeIdToState = [];

	public function register(BlockState $state, int $runtimeId): void
	{
		$key = $state->getKey();

		$this->stateToRuntimeId[$key] = $runtimeId;
		$this->runtimeIdToState[$runtimeId] = $state;
	}

	public function getRuntimeId(BlockState $state): int
	{
		$key = $state->getKey();

		if (!isset($this->stateToRuntimeId[$key])) {
			throw new RuntimeException(
				'Unknown block state: ' . $key
			);
		}

		return $this->stateToRuntimeId[$key];
	}

	public function getState(int $runtimeId): BlockState
	{
		if (!isset($this->runtimeIdToState[$runtimeId])) {
			throw new RuntimeException(
				'Unknown runtime ID: ' . $runtimeId
			);
		}

		return $this->runtimeIdToState[$runtimeId];
	}

	public function hasState(BlockState $state): bool
	{
		return isset($this->stateToRuntimeId[$state->getKey()]);
	}

	public function hasRuntimeId(int $runtimeId): bool
	{
		return isset($this->runtimeIdToState[$runtimeId]);
	}

	public function count(): int
	{
		return count($this->stateToRuntimeId);
	}
}