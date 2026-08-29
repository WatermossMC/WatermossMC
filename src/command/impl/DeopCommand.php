<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\player\OperatorManager;
use watermossmc\Server;
use watermossmc\util\Permission;

final class DeopCommand extends Command
{
    public function __construct()
    {
        parent::__construct('deop', 'Revokes operator status from a player', '/deop <player>', Permission::ROLE_OPERATOR);
    }

    public function execute(\watermossmc\command\CommandSender $sender, array $args): void
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
