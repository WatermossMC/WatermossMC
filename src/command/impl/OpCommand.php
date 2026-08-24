<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\player\OperatorManager;
use watermossmc\Server;
use watermossmc\util\Permission;

final class OpCommand extends Command
{
    public function __construct()
    {
        parent::__construct('op', 'Grants operator status to a player', '/op <player>', Permission::ROLE_OPERATOR);
    }

    public function execute(mixed $sender, array $args): void
    {
        $name = $args[0] ?? '';
        if ($name === '') {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        OperatorManager::setOp($name, Permission::ROLE_OPERATOR);
        Server::getInstance()?->getPlayer($name)?->setRole(Permission::ROLE_OPERATOR);
        $this->sendMessage($sender, 'Made ' . $name . ' a server operator.');
    }
}
