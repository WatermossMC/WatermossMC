<?php

namespace WatermossMC\Plugin;

class PluginLoader
{
    /**
     * @var array<string, array{name: string, version: string, main: string}>
     */
    private array $plugins = [];

    public function __construct()
    {
        echo "Initializing PluginLoader...\n";
    }

    public function loadPlugins(): void
    {
        $pluginDir = __DIR__ . '/../../resources/plugins/';
        
        if (!is_dir($pluginDir) && !mkdir($pluginDir, 0777, true)) {
            echo "Error: Could not create plugin directory: " . $pluginDir . "\n";
            return;
        }
        $pluginPaths = glob($pluginDir . '*', GLOB_ONLYDIR);
        
        if ($pluginPaths === false) {
             echo "Error: Failed to read plugin directory contents.\n";
             return;
        }

        foreach ($pluginPaths as $pluginPath) {
            $pluginFile = $pluginPath . '/plugin.json';
            
            if (file_exists($pluginFile)) {
                $content = file_get_contents($pluginFile);
                
                if ($content === false) {
                    echo "Error reading plugin file: " . $pluginFile . "\n";
                    continue;
                }

                $pluginConfig = json_decode($content, true);

                if (!is_array($pluginConfig) || !isset($pluginConfig['name']) || !is_string($pluginConfig['name'])) {
                    echo "Warning: Invalid or malformed plugin config in " . $pluginFile . "\n";
                    continue;
                }

                $this->plugins[$pluginConfig['name']] = $pluginConfig; 
                echo "Loaded plugin: " . $pluginConfig['name'] . "\n";
            }
        }
    }

    /**
     * @return array<string, array{name: string, version: string, main: string}>
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
