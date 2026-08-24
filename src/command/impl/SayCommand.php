<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\Server;
use watermossmc\util\Permission;

final class SayCommand extends Command
{
    public function __construct()
    {
        parent::__construct('say', 'Broadcasts a message to all players', '/say <message>', Permission::ROLE_OPERATOR);
    }

    public function execute(mixed $sender, array $args): void
    {
        if ($args === []) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        $source = $sender instanceof \watermossmc\player\Player ? $sender->getName() : 'Server';
        Server::getInstance()?->broadcastMessage('[' . $source . '] ' . implode(' ', $args));
    }
}
