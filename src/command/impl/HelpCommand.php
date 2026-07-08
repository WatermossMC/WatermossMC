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

namespace watermossmc\command\impl;

use watermossmc\command\Command;
<<<<<<< HEAD
use watermossmc\player\Player;
=======
use watermossmc\Server;
>>>>>>> 866a1c0 (...)

final class HelpCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'help',
<<<<<<< HEAD
            'Lists available commands',
            '/help'
=======
            'Lists all available commands',
            '/help [command]',
            \watermossmc\util\Permission::ROLE_VISITOR
>>>>>>> 866a1c0 (...)
        );
    }

    public function execute(mixed $sender, array $args): void
    {
<<<<<<< HEAD
        $this->sendMessage($sender, '--- Available Commands ---');

        // In a real scenario, we would get these from the CommandMap
        // For now, we just list the basics
        $this->sendMessage($sender, '/help - Lists available commands');
        $this->sendMessage($sender, '/stop - Stops the server (Console only)');

        if ($sender instanceof Player) {
            $this->sendMessage($sender, '/tp <player> - Teleports you to a player');
        }
=======
        if (count($args) > 0) {
            $commandName = strtolower($args[0]);
            $server = Server::getInstance();
            if ($server === null) {
                return;
            }

            $commands = $server->getCommandMap()->getCommands();
            $command = $commands[$commandName] ?? null;

            if ($command === null) {
                $this->sendMessage($sender, "Command not found: /$commandName");
                return;
            }

            $this->sendMessage($sender, "Usage: /{$command->name} {$command->usage}");
            $this->sendMessage($sender, "Description: {$command->description}");
            return;
        }

        $server = Server::getInstance();
        if ($server === null) {
            return;
        }

        $commands = $server->getCommandMap()->getCommands();

        $this->sendMessage($sender, "--- Available Commands ---");
        foreach ($commands as $cmd) {
            // Only show commands the sender has permission to use
            $senderRole = ($sender instanceof \watermossmc\player\Player)
                ? $sender->getRole()
                : \watermossmc\util\Permission::ROLE_OPERATOR;

            if ($senderRole >= $cmd->requiredRole) {
                $this->sendMessage($sender, "/{$cmd->name} - {$cmd->description}");
            }
        }
        $this->sendMessage($sender, "-------------------------");
>>>>>>> 866a1c0 (...)
    }
}
