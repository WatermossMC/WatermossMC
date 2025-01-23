<?php

namespace WatermossMC\Command;

use WatermossMC\Server;

class CommandManager
{
    private $server;
    private $commands = [];

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->registerDefaultCommands();
    }

    public function registerCommand(Command $command)
    {
        $this->commands[$command->getName()] = $command;
    }

    public function handleCommand(string $input): bool
    {
        $parts = explode(" ", $input);
        $commandName = array_shift($parts);
        $args = $parts;

        if (isset($this->commands[$commandName])) {
            $this->commands[$commandName]->execute($args);
            return true;
        }

        return false;
    }

    private function registerDefaultCommands()
    {
        $this->registerCommand(new StopCommand($this->server));
        $this->registerCommand(new HelpCommand($this->server));
    }
}
