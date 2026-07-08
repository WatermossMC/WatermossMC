<?php

<<<<<<< HEAD
=======
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

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\command;

<<<<<<< HEAD
use watermossmc\player\Player;
use watermossmc\Server;
use watermossmc\util\Logger;
=======
use Throwable;
use watermossmc\player\Player;
use watermossmc\util\Logger;
use watermossmc\util\Permission;
>>>>>>> 866a1c0 (...)

final class CommandMap
{
    /** @var array<string, Command> */
    private array $commands = [];

<<<<<<< HEAD
    public function __construct(
        private readonly Server $server
    ) {}
=======
    public function __construct() {}
>>>>>>> 866a1c0 (...)

    public function register(Command $command): void
    {
        $this->commands[strtolower($command->name)] = $command;
    }

    public function unregister(string $name): void
    {
        unset($this->commands[strtolower($name)]);
    }

    /**
     * @param Player|null $sender
     * @param string $commandLine
     */
    public function execute(mixed $sender, string $commandLine): void
    {
        $parts = explode(' ', trim($commandLine));
        $commandName = strtolower(array_shift($parts) ?? '');

        if ($commandName === '') {
            return;
        }

        $command = $this->commands[$commandName] ?? null;

        if ($command === null) {
            if ($sender instanceof Player) {
                $sender->sendMessage("Unknown command. Type /help for help.");
            } else {
<<<<<<< HEAD
                echo "Unknown command: $commandName
";
=======
                echo "Unknown command: $commandName\n";
            }
            return;
        }

        // Permission Check
        $senderRole = ($sender instanceof Player) ? $sender->getRole() : Permission::ROLE_OPERATOR;
        if ($senderRole < $command->requiredRole) {
            if ($sender instanceof Player) {
                $sender->sendMessage("You do not have permission to execute this command.");
            } else {
                echo "Insufficient permission level.\n";
>>>>>>> 866a1c0 (...)
            }
            return;
        }

        try {
            $command->execute($sender, $parts);
<<<<<<< HEAD
        } catch (\Throwable $e) {
=======


        } catch (Throwable $e) {
>>>>>>> 866a1c0 (...)
            Logger::error("Error executing command /$commandName: " . $e->getMessage());
            if ($sender instanceof Player) {
                $sender->sendMessage("An internal error occurred while executing this command.");
            }
        }
    }

    /**
     * @return array<string, Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
