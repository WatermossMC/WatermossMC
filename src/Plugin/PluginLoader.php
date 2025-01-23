<?php

namespace WatermossMC\Plugin;

class PluginLoader
{
    private $plugins = [];

    public function __construct()
    {
        echo "Initializing PluginLoader...\n";
    }

    public function loadPlugins()
    {
        $pluginDir = __DIR__ . '/../../resources/plugins/';
        if (!is_dir($pluginDir)) {
            mkdir($pluginDir, 0777, true);
        }

        foreach (glob($pluginDir . '*', GLOB_ONLYDIR) as $pluginPath) {
            $pluginFile = $pluginPath . '/plugin.json';
            if (file_exists($pluginFile)) {
                $pluginConfig = json_decode(file_get_contents($pluginFile), true);
                $this->plugins[$pluginConfig['name']] = $pluginConfig;
                echo "Loaded plugin: " . $pluginConfig['name'] . "\n";
            }
        }
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
