<?php

namespace WatermossMC;

use WatermossMC\console\Console;
use WatermossMC\Command\CommandManager;
use WatermossMC\Plugin\PluginLoader;
use WatermossMC\ResourcePack\ResourcePackManager;
use WatermossMC\BehaviorPack\BehaviorPackManager;
use WatermossMC\World\World;
use raklib\server\Server as RakLibServer;

class Server
{
    private Console $console;
    private CommandManager $commandManager;
    private PluginLoader $pluginLoader;
    private ResourcePackManager $resourcePackManager;
    private BehaviorPackManager $behaviorPackManager;
    private World $world;
    private RakLibServer $raklibServer;

    public function __construct()
    {
        echo "Starting WatermossMC...\n";

        $this->loadServerProperties();

        $this->console = new Console($this);
        $this->commandManager = new CommandManager($this);
        $this->pluginLoader = new PluginLoader();
        $this->resourcePackManager = new ResourcePackManager(__DIR__ . '/../resources/resource_packs');
        $this->behaviorPackManager = new BehaviorPackManager(__DIR__ . '/../resources/behavior_packs');
        $this->world = new World(__DIR__ . '/../resources/world', 12345);
        $this->raklibServer = new RakLibServer("0.0.0.0", 19132, "WatermossMC");

        $this->raklibServer->start();
        $this->pluginLoader->loadPlugins();
        $this->mainLoop();
    }

    private function loadServerProperties(): void
    {
        $file = __DIR__ . '/../resources/server.properties';
        if (!file_exists($file)) {
            exit("server.properties not found.\n");
        }

        $config = parse_ini_file($file);

        if (!is_array($config)) {
             echo "Error: Failed to parse server.properties.\n";
             return;
        }

        $motd = $config['motd'] ?? 'A WatermossMC Server';
        $onlineMode = $config['online_mode'] ?? true;
        
        echo "MOTD: " . $motd . "\n";
        echo "Online Mode: " . ($onlineMode ? "Enabled" : "Disabled") . "\n";
    }

    private function mainLoop(): void
    {
        while (true) {
            $this->raklibServer->tick();

            $this->world->tick();

            $this->console->handleInput();
            usleep(50000); // 50ms
        }
    }
    
    public function getCommandManager(): CommandManager
    {
        return $this->commandManager;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function getPluginLoader(): PluginLoader
    {
        return $this->pluginLoader;
    }

    public function getResourcePackManager(): ResourcePackManager
    {
        return $this->resourcePackManager;
    }

    public function getBehaviorPackManager(): BehaviorPackManager
    {
        return $this->behaviorPackManager;
    }
}
