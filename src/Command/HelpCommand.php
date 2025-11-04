<?php

namespace WatermossMC\Command;

use WatermossMC\Server;

class HelpCommand extends Command
{
    private Server $server;

    public function __construct(Server $server)
    {
        parent::__construct("help", "Displays the list of available commands.");
        $this->server = $server;
    }

    public function execute(array $args): void
    {
        echo "Available commands:\n";
        foreach ($this->server->getCommandManager()->getCommands() as $command) {
            echo "- " . $command->getName() . ": " . $command->getDescription() . "\n";
        }
    }
}
