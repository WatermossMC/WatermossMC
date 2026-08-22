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

namespace watermossmc;

use watermossmc\block\BlockInitializer;
use watermossmc\block\BlockRuntimeData;
use watermossmc\command\CommandMap;
use watermossmc\command\CommandRegistry;
use watermossmc\event\Event;
use watermossmc\event\EventDispatcher;
use watermossmc\event\ServerStartEvent;
use watermossmc\event\ServerStopEvent;
use watermossmc\event\TickEvent;
use watermossmc\event\WorldLoadEvent;
use watermossmc\item\ItemInitializer;
use watermossmc\mcpe\network\TickLoop;
use watermossmc\mcpe\PacketDispatcher;
use watermossmc\mcpe\protocol\clientbound\SetTime;
use watermossmc\player\OperatorManager;
use watermossmc\player\Player;
use watermossmc\player\PlayerManager;
use watermossmc\plugin\PluginBase;
use watermossmc\plugin\PluginManager;
use watermossmc\util\Config;
use watermossmc\world\World;

final class Server
{
    private static ?Server $instance = null;

    private EventDispatcher $events;

    private PluginManager $plugins;

    private CommandMap $commands;

    private ?World $world = null;

    private int $currentTick = 0;

    private bool $running = false;

    public function __construct(private readonly string $rootPath, private readonly TickLoop $tickLoop)
    {
        self::$instance = $this;
        $this->events = new EventDispatcher();
        $this->plugins = new PluginManager($this, $this->rootPath . '/plugins');
        $this->commands = new CommandMap();
    }

    public static function getInstance(): ?Server
    {
        return self::$instance;
    }

    public function boot(): void
    {
        BlockInitializer::init();
        BlockRuntimeData::init(__DIR__ . "/../resources/canonical_block_states.nbt");
        ItemInitializer::init();
        foreach (CommandRegistry::getCommands() as $commandClass) {
            /** @var \watermossmc\command\Command $command */
            $command = new $commandClass();
            $this->commands->register($command);
        }
        OperatorManager::load($this);
        $this->running = true;
        $this->plugins->loadPlugins();
        $this->plugins->enablePlugins();
        $this->dispatch(new ServerStartEvent($this));
    }

    public function shutdown(): void
    {
        $this->running = false;
        $this->dispatch(new ServerStopEvent($this));
        OperatorManager::save($this);
        $this->saveWorld();
        $this->plugins->disablePlugins();
    }

    public function tick(): void
    {
        $this->currentTick++;
        $this->getWorld()->tickTime();
        $this->getWorld()->getEntityManager()->tick();
        PacketDispatcher::syncPlayers();
        if ($this->currentTick % 20 === 0) {
            $time = $this->getWorld()->getDayTime();
            foreach ($this->getOnlinePlayers() as $player) {
                $socket = $player->session->getSocket();
                if ($socket !== null) {
                    SetTime::send($player->session, $socket, $time);
                }
            }
        }
        $this->dispatch(new TickEvent($this, $this->currentTick));
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function getName(): string
    {
        return Config::getString("server_name", "WatermossMC");
    }

    public function getMotd(): string
    {
        return Config::getString("motd", "WatermossMC Server");
    }

    public function getVersionName(): string
    {
        return Config::getString("version_name", "1.21.124");
    }

    public function getBindAddress(): string
    {
        return Config::getString("server_ip", "0.0.0.0");
    }

    public function getBindPort(): int
    {
        return Config::getInt("server_port", 19132);
    }

    public function getTickLoop(): TickLoop
    {
        return $this->tickLoop;
    }

    public function getCurrentTick(): int
    {
        return $this->currentTick;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->events;
    }

    public function getPluginManager(): PluginManager
    {
        return $this->plugins;
    }

    public function getCommandMap(): CommandMap
    {
        return $this->commands;
    }

    public function getPlugin(string $name): ?PluginBase
    {
        return $this->plugins->getPlugin($name);
    }

    public function dispatchCommand(mixed $sender, string $commandLine): void
    {
        $this->commands->execute($sender, $commandLine);
    }

    /**
     * @return array<string, PluginBase>
     */
    public function getPlugins(): array
    {
        return $this->plugins->getPlugins();
    }

    public function dispatch(Event $event): Event
    {
        return $this->events->dispatch($event);
    }

    /**
     * @param class-string<Event> $eventClass
     * @param callable(Event): void $listener
     */
    public function on(string $eventClass, callable $listener): void
    {
        $this->events->listen($eventClass, $listener);
    }

    public function getWorld(): World
    {
        if ($this->world !== null) {
            return $this->world;
        }
        $worldName = Config::getString('level_name', 'world');
        $worldFolder = Config::getString('world_folder', $worldName);
        $worldPath = $this->rootPath . '/' . $worldFolder;
        $this->world = World::load($worldPath, $worldName, Config::getInt('level_seed', 12345));
        $this->dispatch(new WorldLoadEvent($this, $this->world));
        return $this->world;
    }

    public function saveWorld(): void
    {
        $this->world?->save();
    }

    public function reloadWorld(): World
    {
        $this->saveWorld();
        $this->world = null;
        return $this->getWorld();
    }

    /**
     * @return array<string, Player>
     */
    public function getOnlinePlayers(): array
    {
        return PlayerManager::all();
    }

    public function getOnlinePlayerCount(): int
    {
        return PlayerManager::count();
    }

    public function getMaxPlayers(): int
    {
        return Config::getInt("max_players", 20);
    }

    public function getPlayer(string $nameOrUuid): ?Player
    {
        return PlayerManager::getByUuid($nameOrUuid) ?? PlayerManager::getByName($nameOrUuid);
    }

    public function broadcastMessage(string $message): int
    {
        $sent = 0;
        foreach ($this->getOnlinePlayers() as $player) {
            if ($player->sendMessage($message)) {
                $sent++;
            }
        }
        return $sent;
    }

    /**
     * @param callable(int): void $task
     */
    public function scheduleDelayedTask(int $ticks, callable $task): int
    {
        return $this->tickLoop->delay($ticks, $task);
    }

    /**
     * @param callable(int): void $task
     */
    public function scheduleRepeatingTask(int $intervalTicks, callable $task, int $initialDelay = 1): int
    {
        return $this->tickLoop->repeat($intervalTicks, $task, $initialDelay);
    }

    public function cancelTask(int $taskId): void
    {
        $this->tickLoop->cancel($taskId);
    }

    public function getConfig(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }

    public function getConfigString(string $key, string $default = ""): string
    {
        return Config::getString($key, $default);
    }

    public function getConfigInt(string $key, int $default = 0): int
    {
        return Config::getInt($key, $default);
    }

    public function getConfigBool(string $key, bool $default = false): bool
    {
        return Config::getBool($key, $default);
    }

    /**
     * @return array{name: string, motd: string, onlinePlayers: int, maxPlayers: int, tick: int, running: bool}
     */
    public function getStatus(): array
    {
        return ["name" => $this->getName(), "motd" => $this->getMotd(), "onlinePlayers" => $this->getOnlinePlayerCount(), "maxPlayers" => $this->getMaxPlayers(), "tick" => $this->currentTick, "running" => $this->running];
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}
