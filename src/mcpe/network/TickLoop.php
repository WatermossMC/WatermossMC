<?php

<<<<<<< HEAD
=======
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

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\mcpe\network;

final class TickLoop
{
    /** @var callable[] */
    private array $tasks = [];

    /** @var array<int, array{callback: callable(int): void, nextRun: int, delay: int, repeating: bool}> */
    private array $scheduledTasks = [];

    private int $nextTaskId = 1;

    private int $currentTick = 0;

    public function add(callable $task): void
    {
        $this->tasks[] = $task;
    }

    /**
     * @param callable(int): void $task
     */
    public function delay(int $ticks, callable $task): int
    {
        $ticks = max(1, $ticks);
        return $this->schedule($ticks, $ticks, false, $task);
    }

    /**
     * @param callable(int): void $task
     */
    public function repeat(int $intervalTicks, callable $task, int $initialDelay = 1): int
    {
        $intervalTicks = max(1, $intervalTicks);
        $initialDelay = max(1, $initialDelay);

        return $this->schedule($initialDelay, $intervalTicks, true, $task);
    }

    public function cancel(int $taskId): void
    {
        unset($this->scheduledTasks[$taskId]);
    }

    public function getCurrentTick(): int
    {
        return $this->currentTick;
    }

    public function runOnce(): void
    {
        $this->currentTick++;

        foreach ($this->tasks as $task) {
            $task();
        }

        foreach ($this->scheduledTasks as $taskId => $task) {
            if ($task["nextRun"] > $this->currentTick) {
                continue;
            }

            $task["callback"]($this->currentTick);

            if ($task["repeating"]) {
                $this->scheduledTasks[$taskId]["nextRun"] = $this->currentTick + $task["delay"];
            } else {
                unset($this->scheduledTasks[$taskId]);
            }
        }
    }

    /**
     * @param callable(int): void $task
     */
    private function schedule(int $initialDelay, int $delay, bool $repeating, callable $task): int
    {
        $taskId = $this->nextTaskId++;
        $this->scheduledTasks[$taskId] = [
            "callback" => $task,
            "nextRun" => $this->currentTick + $initialDelay,
            "delay" => $delay,
            "repeating" => $repeating,
        ];

        return $taskId;
    }
}
