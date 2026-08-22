<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

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
