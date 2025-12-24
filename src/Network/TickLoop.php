<?php

declare(strict_types=1);

namespace WatermossMC\Network;

final class TickLoop
{
    /** @var callable[] */
    private array $tasks = [];

    public function add(callable $task): void
    {
        $this->tasks[] = $task;
    }

    public function runOnce(): void
    {
        foreach ($this->tasks as $task) {
            $task();
        }
    }
}
