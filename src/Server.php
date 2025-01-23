<?php

namespace WatermossMC;

use WatermossMC\Console;
use WatermossMC\Command\CommandManager;
use WatermossMC\Plugin\PluginLoader;
use WatermossMC\ResourcePack\ResourcePackManager;
use WatermossMC\BehaviorPack\BehaviorPackManager;
use WatermossMC\World\World;
use raknet\server\RakNetServer;

class Server
{
    private $console;
    private $commandManager;
    private $pluginLoader;
    private $resourcePackManager;
    private $behaviorPackManager;
    private $world;
    private $raknetServer;

    public function __construct()
    {
        echo "Starting WatermossMC...\n";

        // Load configurations
        $this->loadServerProperties();

        // Initialize components
        $this->console = new Console($this);
        $this->commandManager = new CommandManager($this);
        $this->pluginLoader = new PluginLoader($this);
        $this->resourcePackManager = new ResourcePackManager(__DIR__ . '/../resources/resource_packs');
        $this->behaviorPackManager = new BehaviorPackManager(__DIR__ . '/../resources/behavior_packs');
        $this->world = new World(__DIR__ . '/../resources/world', 12345);
        $this->raknetServer = new RakNetServer("0.0.0.0", 19132, "WatermossMC");

        // Start server
        $this->raknetServer->start();
        $this->pluginLoader->loadPlugins();
        $this->mainLoop();
    }

    private function loadServerProperties()
    {
        $file = __DIR__ . '/../resources/server.properties';
        if (!file_exists($file)) {
            die("server.properties not found.\n");
        }

        $config = parse_ini_file($file);
        echo "MOTD: " . $config['motd'] . "\n";
        echo "Online Mode: " . ($config['online_mode'] ? "Enabled" : "Disabled") . "\n";
    }

    private function mainLoop()
    {
        while (true) {
            $this->raknetServer->tick();

            // Update world and entities
            $this->world->tick();

            // Handle console input
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
