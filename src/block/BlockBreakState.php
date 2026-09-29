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

/**
 * Tracks the block a player is currently mining: where it is, when the break started, and how
 * long it should take. Used to drive the break animation (BLOCK_START_BREAK / BLOCK_BREAK_SPEED /
 * BLOCK_STOP_BREAK level events) and to validate ACTION_STOP_BREAK server-side rather than
 * trusting the client's timing.
 */
final class BlockBreakState
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
        public readonly int $z,
        public readonly float $startedAt,
        public readonly float $breakTimeSeconds
    ) {}

    public function isAtSameBlock(int $x, int $y, int $z): bool
    {
        return $this->x === $x && $this->y === $y && $this->z === $z;
    }

    public function isComplete(float $now): bool
    {
        return ($now - $this->startedAt) >= $this->breakTimeSeconds;
    }

    /**
     * 0-65535 scale expected by LevelEvent::BLOCK_BREAK_SPEED's eventData (fraction of a full
     * "tick rate" progress bar), clamped to the valid range.
     */
    public function getProgressEventData(float $now): int
    {
        if ($this->breakTimeSeconds <= 0.0) {
            return 65535;
        }
        $fraction = ($now - $this->startedAt) / $this->breakTimeSeconds;
        return (int) max(0, min(65535, (int) round($fraction * 65535)));
    }
}
