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

namespace watermossmc\plugin;

use LogicException;
use RuntimeException;
use watermossmc\command\Command;
use watermossmc\event\Event;
use watermossmc\Server;
use watermossmc\util\Logger;

abstract class PluginBase
{
    /** @var array<int, true> */
    private array $taskIds = [];

    /** @var array<int, true> */
    private array $listenerIds = [];

    /** @var array<string, Command> */
    private array $commandNames = [];

    private ?PluginConfig $config = null;

    /** @var array<string, mixed> */
    private array $configDefaults = [];

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

    public function isEnabled(): bool
    {
        return $this->server->getPluginManager()->isPluginEnabled($this);
    }

    public function getConfig(): PluginConfig
    {
        return $this->config ??= new PluginConfig($this->dataFolder . '/config.json', $this->configDefaults);
    }

    /**
     * Replaces the defaults used by this plugin's config before it is loaded.
     * Call this from onLoad() before the first call to getConfig().
     *
     * @param array<string, mixed> $defaults
     */
    public function setConfigDefaults(array $defaults): void
    {
        if ($this->config !== null) {
            throw new LogicException('Config defaults must be set before the config is loaded');
        }
        $this->configDefaults = $defaults;
    }

    public function reloadConfig(): void
    {
        $this->getConfig()->reload();
    }

    public function saveConfig(): void
    {
        $this->getConfig()->save();
    }

    /**
     * Copies resources/config.json to the data directory once, if it exists.
     */
    public function saveDefaultConfig(): bool
    {
        $source = dirname($this->dataFolder) . '/resources/config.json';
        $target = $this->dataFolder . '/config.json';
        if (is_file($target) || !is_file($source)) {
            return false;
        }
        if (!is_dir($this->dataFolder) && !mkdir($this->dataFolder, 0o777, true) && !is_dir($this->dataFolder)) {
            throw new RuntimeException('Unable to create data folder: ' . $this->dataFolder);
        }
        if (!copy($source, $target)) {
            throw new RuntimeException('Unable to copy default plugin config: ' . $source);
        }
        $this->config?->reload();
        return true;
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
        $taskId = 0;
        $taskId = $this->server->scheduleDelayedTask($ticks, function (int $currentTick) use (&$taskId, $task): void {
            unset($this->taskIds[$taskId]);
            $task($currentTick);
        });
        $this->taskIds[$taskId] = true;
        return $taskId;
    }

    /**
     * @param callable(int): void $task
     */
    public function scheduleRepeatingTask(int $intervalTicks, callable $task, int $initialDelay = 1): int
    {
        $taskId = $this->server->scheduleRepeatingTask($intervalTicks, $task, $initialDelay);
        $this->taskIds[$taskId] = true;
        return $taskId;
    }

    public function cancelTask(int $taskId): void
    {
        $this->server->cancelTask($taskId);
        unset($this->taskIds[$taskId]);
    }

    /**
     * @param class-string<Event> $eventClass
     * @param callable(Event): void $listener
     */
    public function listen(string $eventClass, callable $listener): int
    {
        $listenerId = $this->server->getEventDispatcher()->listen($eventClass, $listener);
        $this->listenerIds[$listenerId] = true;
        return $listenerId;
    }

    /**
     * Alias of listen(), for code that reads better as a subscription.
     *
     * @param class-string<Event> $eventClass
     * @param callable(Event): void $listener
     */
    public function on(string $eventClass, callable $listener): int
    {
        return $this->listen($eventClass, $listener);
    }

    public function unlisten(int $listenerId): void
    {
        $this->server->getEventDispatcher()->unlisten($listenerId);
        unset($this->listenerIds[$listenerId]);
    }

    /** Alias of unlisten(). */
    public function off(int $listenerId): void
    {
        $this->unlisten($listenerId);
    }

    public function registerCommand(Command $command): void
    {
        if (!$this->server->getCommandMap()->register($command)) {
            throw new LogicException('A command named ' . $command->name . ' or one of its aliases is already registered');
        }
        $this->commandNames[strtolower($command->name)] = $command;
    }

    public function unregisterCommand(string $name): void
    {
        $this->server->getCommandMap()->unregister($name);
        unset($this->commandNames[strtolower($name)]);
    }

    protected function info(string $message): void
    {
        Logger::info('[' . $this->description->name . '] ' . $message);
    }

    protected function warning(string $message): void
    {
        Logger::warning('[' . $this->description->name . '] ' . $message);
    }

    /** @internal Called only by PluginManager after onDisable(). */
    final public function clearRuntimeResources(): void
    {
        foreach (array_keys($this->taskIds) as $taskId) {
            $this->server->cancelTask($taskId);
        }
        $this->taskIds = [];

        foreach (array_keys($this->listenerIds) as $listenerId) {
            $this->server->getEventDispatcher()->unlisten($listenerId);
        }
        $this->listenerIds = [];

        foreach ($this->commandNames as $commandName => $command) {
            if ($this->server->getCommandMap()->getCommand($commandName) === $command) {
                $this->server->getCommandMap()->unregister($commandName);
            }
        }
        $this->commandNames = [];
    }
}
