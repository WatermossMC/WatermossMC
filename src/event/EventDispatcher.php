<?php

declare(strict_types=1);

namespace watermossmc\event;

use Throwable;
use watermossmc\util\Logger;

final class EventDispatcher
{
    /** @var array<class-string<Event>, list<callable(Event): void>> */
    private array $listeners = [];

    /**
     * @param class-string<Event> $eventClass
     * @param callable(Event): void $listener
     */
    public function listen(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(Event $event): Event
    {
        foreach ($this->listeners as $eventClass => $listeners) {
            if (!$event instanceof $eventClass) {
                continue;
            }

            foreach ($listeners as $listener) {
                try {
                    $listener($event);
                } catch (Throwable $e) {
                    Logger::error('Event listener failed for ' . $event::class . ': ' . $e->getMessage());
                }
            }
        }

        return $event;
    }
}
