<?php

declare(strict_types=1);

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\player\Player;

final class HelpCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'help',
            'Lists available commands',
            '/help'
        );
    }

    public function execute(mixed $sender, array $args): void
    {
        $this->sendMessage($sender, '--- Available Commands ---');

        // In a real scenario, we would get these from the CommandMap
        // For now, we just list the basics
        $this->sendMessage($sender, '/help - Lists available commands');
        $this->sendMessage($sender, '/stop - Stops the server (Console only)');

        if ($sender instanceof Player) {
            $this->sendMessage($sender, '/tp <player> - Teleports you to a player');
        }
    }
}
