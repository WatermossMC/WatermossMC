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

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\command\CommandSender;
use watermossmc\player\Player;
use watermossmc\Server;
use watermossmc\util\Permission;

final class HelpCommand extends Command
{
    public function __construct()
    {
        parent::__construct('help', 'Lists all available commands', '/help [command]', Permission::ROLE_VISITOR);
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (count($args) > 0) {
            $commandName = strtolower($args[0]);
            $server = Server::getInstance();
            if ($server === null) {
                return;
            }
            $commands = $server->getCommandMap()->getCommands();
            $command = $commands[$commandName] ?? null;
            if ($command === null) {
                $this->sendMessage($sender, "Command not found: /{$commandName}");
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
            $senderRole = $sender instanceof Player ? $sender->getRole() : Permission::ROLE_OPERATOR;
            if ($senderRole >= $cmd->requiredRole) {
                $this->sendMessage($sender, "/{$cmd->name} - {$cmd->description}");
            }
        }
        $this->sendMessage($sender, "-------------------------");
    }
}
