<?php

namespace WatermossMC\Command;

use WatermossMC\Server;

class CommandManager
{
    private Server $server;

    /**
     * @var Command[]|array<string, Command>
     */
    private array $commands = [];

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->registerDefaultCommands();
    }

    public function registerCommand(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function handleCommand(string $input): bool
    {
        $parts = explode(" ", $input);
        /** @var string $commandName */
        $commandName = array_shift($parts);
        $args = $parts;

        if (isset($this->commands[$commandName])) {
            /** @var Command $command */
            $command = $this->commands[$commandName];

            $command->execute($args); 
            return true;
        }

        return false;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    private function registerDefaultCommands(): void
    {
        $this->registerCommand(new StopCommand($this->server));
        $this->registerCommand(new HelpCommand($this->server));
    }
}
