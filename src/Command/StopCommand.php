<?php

namespace WatermossMC\Command;

use WatermossMC\Server;

class StopCommand extends Command
{
    private $server;

    public function __construct(Server $server)
    {
        parent::__construct("stop", "Stops the server.");
        $this->server = $server;
    }

    public function execute(array $args): void
    {
        echo "Stopping server...\n";
        exit(0);
    }
}
