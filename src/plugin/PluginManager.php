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

use RuntimeException;
use Throwable;
use watermossmc\Server;
use watermossmc\util\Logger;

final class PluginManager
{
    /** @var array<string, PluginBase> */
    private array $plugins = [];

    /** @var array<string, true> */
    private array $enabledPlugins = [];

    /** @var array<string, true> */
    private array $enablingPlugins = [];

    public function __construct(private readonly Server $server, private readonly string $pluginPath) {}

    public function loadPlugins(): void
    {
        $this->ensurePluginDirectory();
        $directories = glob($this->pluginPath . '/*', \GLOB_ONLYDIR);
        if ($directories === false) {
            Logger::warning('Unable to scan plugin directory: ' . $this->pluginPath);
            return;
        }
        foreach ($directories as $directory) {
            $this->loadPlugin($directory);
        }
    }

    public function enablePlugins(): void
    {
        foreach ($this->plugins as $plugin) {
            $this->enablePlugin($plugin);
        }
    }

    public function disablePlugins(): void
    {
        foreach (array_reverse(array_values($this->plugins)) as $plugin) {
            $this->disablePlugin($plugin);
        }
    }

    /**
     * @return array<string, PluginBase>
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function getPlugin(string $name): ?PluginBase
    {
        return $this->plugins[strtolower($name)] ?? null;
    }

    public function getPluginPath(): string
    {
        return $this->pluginPath;
    }

    public function isPluginEnabled(PluginBase|string $plugin): bool
    {
        $name = $plugin instanceof PluginBase ? $plugin->getName() : $plugin;
        return isset($this->enabledPlugins[strtolower($name)]);
    }

    public function enablePlugin(PluginBase|string $plugin): bool
    {
        $plugin = $this->resolvePlugin($plugin);
        if ($plugin === null || $this->isPluginEnabled($plugin)) {
            return false;
        }
        $pluginKey = strtolower($plugin->getName());
        if (isset($this->enablingPlugins[$pluginKey])) {
            Logger::error('Cannot enable plugin ' . $plugin->getName() . ': circular dependency detected');
            return false;
        }
        $this->enablingPlugins[$pluginKey] = true;
        foreach ($plugin->getDescription()->getDepend() as $dependency) {
            $dependencyPlugin = $this->getPlugin($dependency);
            if ($dependencyPlugin === null || (!$this->isPluginEnabled($dependencyPlugin) && !$this->enablePlugin($dependencyPlugin))) {
                Logger::error('Cannot enable plugin ' . $plugin->getName() . ': missing dependency ' . $dependency);
                unset($this->enablingPlugins[$pluginKey]);
                return false;
            }
        }
        try {
            $plugin->onEnable();
            $this->enabledPlugins[$pluginKey] = true;
            Logger::success('Enabled plugin ' . $plugin->getName() . ' v' . $plugin->getVersion());
            return true;
        } catch (Throwable $e) {
            $plugin->clearRuntimeResources();
            Logger::error('Failed to enable plugin ' . $plugin->getName() . ': ' . $e->getMessage());
            return false;
        } finally {
            unset($this->enablingPlugins[$pluginKey]);
        }
    }

    public function disablePlugin(PluginBase|string $plugin): bool
    {
        $plugin = $this->resolvePlugin($plugin);
        if ($plugin === null || !$this->isPluginEnabled($plugin)) {
            return false;
        }
        try {
            $plugin->onDisable();
        } catch (Throwable $e) {
            Logger::error('Failed to disable plugin ' . $plugin->getName() . ': ' . $e->getMessage());
        } finally {
            $plugin->clearRuntimeResources();
            unset($this->enabledPlugins[strtolower($plugin->getName())]);
        }
        Logger::info('Disabled plugin ' . $plugin->getName());
        return true;
    }

    public function loadPlugin(string $directory): ?PluginBase
    {
        $manifestPath = $directory . '/plugin.json';
        if (!is_file($manifestPath)) {
            return null;
        }
        try {
            $description = $this->readDescription($manifestPath);
            $pluginKey = strtolower($description->name);
            if (isset($this->plugins[$pluginKey])) {
                throw new RuntimeException('A plugin named ' . $description->name . ' is already loaded');
            }
            $mainFile = $directory . '/src/' . str_replace('\\', '/', $description->main) . '.php';
            if (!is_file($mainFile)) {
                $mainFile = $directory . '/' . str_replace('\\', '/', $description->main) . '.php';
            }
            if (!is_file($mainFile)) {
                throw new RuntimeException('Main class file not found for ' . $description->main);
            }
            require_once $mainFile;
            if (!class_exists($description->main)) {
                throw new RuntimeException('Main class not found: ' . $description->main);
            }
            if (!is_subclass_of($description->main, PluginBase::class)) {
                throw new RuntimeException('Main class must extend ' . PluginBase::class);
            }
            $dataFolder = $directory . '/data';
            if (!is_dir($dataFolder) && !mkdir($dataFolder, 0o777, true) && !is_dir($dataFolder)) {
                throw new RuntimeException('Unable to create data folder: ' . $dataFolder);
            }
            /** @var PluginBase $plugin */
            $plugin = new $description->main($this->server, $description, $dataFolder);
            $plugin->onLoad();
            $this->plugins[$pluginKey] = $plugin;
            Logger::info('Loaded plugin ' . $description->name . ' v' . $description->version);
            return $plugin;
        } catch (Throwable $e) {
            Logger::error('Failed to load plugin from ' . basename($directory) . ': ' . $e->getMessage());
            return null;
        }
    }

    private function resolvePlugin(PluginBase|string $plugin): ?PluginBase
    {
        if ($plugin instanceof PluginBase) {
            return $this->plugins[strtolower($plugin->getName())] ?? null;
        }
        return $this->getPlugin($plugin);
    }

    private function readDescription(string $manifestPath): PluginDescription
    {
        $json = file_get_contents($manifestPath);
        if ($json === false) {
            throw new RuntimeException('Unable to read plugin manifest');
        }
        $data = json_decode($json, true);
        if (!\is_array($data)) {
            throw new RuntimeException('Invalid plugin manifest JSON');
        }
        return PluginDescription::fromArray($data, $manifestPath);
    }

    private function ensurePluginDirectory(): void
    {
        if (!is_dir($this->pluginPath) && !mkdir($this->pluginPath, 0o777, true) && !is_dir($this->pluginPath)) {
            throw new RuntimeException('Unable to create plugin directory: ' . $this->pluginPath);
        }
    }
}
