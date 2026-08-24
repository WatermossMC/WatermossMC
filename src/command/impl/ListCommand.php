<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\Server;
use watermossmc\util\Permission;

final class ListCommand extends Command
{
    public function __construct()
    {
        parent::__construct('list', 'Lists online players', '/list', Permission::ROLE_VISITOR);
    }

    public function execute(mixed $sender, array $args): void
    {
        $server = Server::getInstance();
        if ($server === null) {
            return;
        }
        $names = array_map(static fn ($player): string => $player->getName(), $server->getOnlinePlayers());
        $message = 'There are ' . count($names) . ' of a max of ' . $server->getMaxPlayers() . ' players online';
        $this->sendMessage($sender, $names === [] ? $message . '.' : $message . ': ' . implode(', ', $names));
    }
}
