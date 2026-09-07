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
use watermossmc\player\OperatorManager;
use watermossmc\Server;
use watermossmc\util\Permission;

final class DeopCommand extends Command
{
    public function __construct()
    {
        parent::__construct('deop', 'Revokes operator status from a player', '/deop <player>', Permission::ROLE_OPERATOR);
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $name = $args[0] ?? '';
        if ($name === '') {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        if (!OperatorManager::removeOp($name)) {
            $this->sendMessage($sender, $name . ' is not an operator.');
            return;
        }
        Server::getInstance()?->getPlayer($name)?->setRole(Permission::ROLE_MEMBER);
        $this->sendMessage($sender, 'Removed ' . $name . ' from the server operators.');
    }
}
