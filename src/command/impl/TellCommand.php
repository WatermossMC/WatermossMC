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
use watermossmc\player\PlayerManager;
use watermossmc\util\Permission;

final class TellCommand extends Command
{
    public function __construct()
    {
        parent::__construct('tell', 'Sends a private message', '/tell <player> <message>', Permission::ROLE_MEMBER, ['msg', 'w']);
    }

    public function execute(CommandSender $sender, array $args): void
    {
        if (count($args) < 2) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        $target = PlayerManager::getByName($args[0]);
        if ($target === null) {
            $this->sendMessage($sender, 'No player was found.');
            return;
        }
        $source = $sender instanceof Player ? $sender->getName() : 'Server';
        $message = implode(' ', array_slice($args, 1));
        $target->sendMessage('[' . $source . ' -> You] ' . $message);
        if ($sender instanceof Player && $sender !== $target) {
            $sender->sendMessage('[You -> ' . $target->getName() . '] ' . $message);
        }
    }
}
