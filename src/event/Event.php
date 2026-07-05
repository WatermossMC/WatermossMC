<?php

declare(strict_types=1);

namespace watermossmc\event;

abstract class Event
{
    private bool $cancelled = false;

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    public function cancel(): void
    {
        $this->cancelled = true;
    }
}
