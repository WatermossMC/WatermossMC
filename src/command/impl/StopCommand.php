<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\player\Player;

final class StopCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'stop',
            'Stops the server',
            '/stop'
        );
    }

    public function execute(mixed $sender, array $args): void
    {
        if ($sender instanceof Player) {
            $this->sendMessage($sender, "You do not have permission to stop the server.");
            return;
        }

        $this->sendMessage($sender, "Stopping server...");

        $server = \watermossmc\Server::getInstance();
        if ($server !== null) {
            $server->shutdown();
        }
    }
}
