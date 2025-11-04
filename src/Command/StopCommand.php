<?php

namespace WatermossMC\Command;

use WatermossMC\Server;

class StopCommand extends Command
{
    private Server $server;

    public function __construct(Server $server)
    {
        parent::__construct("stop", "Stops the server.");
        $this->server = $server;
    }

    public function execute(array $args): void
    {
        echo "Stopping server...\n";
        // $this->server->shutdown();
        exit(0);
    }
}
