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

namespace watermossmc\command;

use Throwable;
use watermossmc\player\Player;
use watermossmc\util\Logger;
use watermossmc\util\Permission;

final class CommandMap
{
    /** @var array<string, Command> */
    private array $commands = [];

    /** @var array<string, string> Alias to a canonical command name. */
    private array $aliases = [];

    public function __construct() {}

    /**
     * Registers a command and all of its aliases.
     *
     * Returns false if any name is already in use; no partial registration is made.
     */
    public function register(Command $command): bool
    {
        $name = $this->normalizeName($command->name);
        $names = [$name, ...array_map($this->normalizeName(...), $command->getAliases())];
        if (count($names) !== count(array_unique($names))) {
            return false;
        }
        foreach ($names as $commandName) {
            if (isset($this->commands[$commandName]) || isset($this->aliases[$commandName])) {
                return false;
            }
        }

        $this->commands[$name] = $command;
        foreach (array_slice($names, 1) as $alias) {
            $this->aliases[$alias] = $name;
        }
        return true;
    }

    /** Removes a command by its primary name or one of its aliases. */
    public function unregister(string $name): bool
    {
        $name = strtolower($name);
        $canonicalName = $this->aliases[$name] ?? $name;
        if (!isset($this->commands[$canonicalName])) {
            return false;
        }
        unset($this->commands[$canonicalName]);
        foreach ($this->aliases as $alias => $commandName) {
            if ($commandName === $canonicalName) {
                unset($this->aliases[$alias]);
            }
        }
        return true;
    }

    public function getCommand(string $name): ?Command
    {
        $name = strtolower(trim($name));
        return $this->commands[$this->aliases[$name] ?? $name] ?? null;
    }

    public function hasCommand(string $name): bool
    {
        return $this->getCommand($name) !== null;
    }

    /**
     * @param string $commandLine
     */
    public function execute(CommandSender $sender, string $commandLine): bool
    {
        $parts = CommandParser::parse($commandLine);
        $commandName = strtolower(ltrim(array_shift($parts) ?? '', '/'));
        if ($commandName === '') {
            return false;
        }
        $command = $this->getCommand($commandName);
        if ($command === null) {
            $sender->sendMessage("Unknown command. Type /help for help.");
            return false;
        }
        // Permission Check
        if (!$sender->hasPermission($command->requiredRole)) {
            $sender->sendMessage("You do not have permission to execute this command.");
            return false;
        }
        try {
            $command->execute($sender, $parts);
        } catch (Throwable $e) {
            Logger::error("Error executing command /{$commandName}: " . $e->getMessage());
            $sender->sendMessage("An internal error occurred while executing this command.");
            return false;
        }
        return true;
    }

    /**
     * @return array<string, Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @return array<string, string> Maps every alias to its primary command name.
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    private function normalizeName(string $name): string
    {
        $name = strtolower(trim($name));
        if ($name === '' || str_contains($name, ' ')) {
            throw new \InvalidArgumentException('Command names and aliases must be non-empty single words');
        }
        return $name;
    }
}
