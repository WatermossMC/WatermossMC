<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\player\Player;
use watermossmc\player\PlayerManager;
use watermossmc\util\Permission;

final class TellCommand extends Command
{
    public function __construct()
    {
        parent::__construct('tell', 'Sends a private message', '/tell <player> <message>', Permission::ROLE_MEMBER, ['msg', 'w']);
    }

    public function execute(mixed $sender, array $args): void
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
