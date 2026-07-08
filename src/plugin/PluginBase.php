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

declare (strict_types=1);

namespace watermossmc\plugin;

use watermossmc\event\Event;
use watermossmc\Server;
use watermossmc\util\Logger;

abstract class PluginBase
{
    public function __construct(private readonly Server $server, private readonly PluginDescription $description, private readonly string $dataFolder) {}

    public function onLoad(): void {}

    public function onEnable(): void {}

    public function onDisable(): void {}

    public function getServer(): Server
    {
        return $this->server;
    }

    public function getDescription(): PluginDescription
    {
        return $this->description;
    }

    public function getDataFolder(): string
    {
        return $this->dataFolder;
    }

    public function getName(): string
    {
        return $this->description->name;
    }

    public function getVersion(): string
    {
        return $this->description->version;
    }

    public function getApiVersion(): string
    {
        return $this->description->api;
    }

    public function broadcastMessage(string $message): int
    {
        return $this->server->broadcastMessage($message);
    }

    /**
     * @param callable(int): void $task
     */
    public function scheduleDelayedTask(int $ticks, callable $task): int
    {
        return $this->server->scheduleDelayedTask($ticks, $task);
    }

    /**
     * @param callable(int): void $task
     */
    public function scheduleRepeatingTask(int $intervalTicks, callable $task, int $initialDelay = 1): int
    {
        return $this->server->scheduleRepeatingTask($intervalTicks, $task, $initialDelay);
    }

    public function cancelTask(int $taskId): void
    {
        $this->server->cancelTask($taskId);
    }

    /**
     * @param class-string<Event> $eventClass
     * @param callable(Event): void $listener
     */
    protected function listen(string $eventClass, callable $listener): void
    {
        $this->server->getEventDispatcher()->listen($eventClass, $listener);
    }

    protected function info(string $message): void
    {
        Logger::info('[' . $this->description->name . '] ' . $message);
    }

    protected function warning(string $message): void
    {
        Logger::warning('[' . $this->description->name . '] ' . $message);
    }
}
