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

namespace watermossmc\event;

use InvalidArgumentException;
use Throwable;
use watermossmc\util\Logger;

final class EventDispatcher
{
    /** @var array<class-string<Event>, array<int, callable(Event): void>> */
    private array $listeners = [];

    private int $nextListenerId = 1;

    /**
     * @param class-string<Event> $eventClass
     * @param callable(Event): void $listener
     */
    public function listen(string $eventClass, callable $listener): int
    {
        if (!is_a($eventClass, Event::class, true)) {
            throw new InvalidArgumentException("Event listener class must extend " . Event::class);
        }

        $listenerId = $this->nextListenerId++;
        $this->listeners[$eventClass][$listenerId] = $listener;
        return $listenerId;
    }

    public function unlisten(int $listenerId): void
    {
        foreach ($this->listeners as $eventClass => $listeners) {
            if (!isset($listeners[$listenerId])) {
                continue;
            }

            unset($this->listeners[$eventClass][$listenerId]);
            if ($this->listeners[$eventClass] === []) {
                unset($this->listeners[$eventClass]);
            }
            return;
        }
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
