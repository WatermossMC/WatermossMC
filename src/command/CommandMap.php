<?php

declare(strict_types=1);

namespace watermossmc\command;

use watermossmc\player\Player;
use watermossmc\Server;
use watermossmc\util\Logger;

final class CommandMap
{
    /** @var array<string, Command> */
    private array $commands = [];

    public function __construct(
        private readonly Server $server
    ) {}

    public function register(Command $command): void
    {
        $this->commands[strtolower($command->name)] = $command;
    }

    public function unregister(string $name): void
    {
        unset($this->commands[strtolower($name)]);
    }

    /**
     * @param Player|null $sender
     * @param string $commandLine
     */
    public function execute(mixed $sender, string $commandLine): void
    {
        $parts = explode(' ', trim($commandLine));
        $commandName = strtolower(array_shift($parts) ?? '');

        if ($commandName === '') {
            return;
        }

        $command = $this->commands[$commandName] ?? null;

        if ($command === null) {
            if ($sender instanceof Player) {
                $sender->sendMessage("Unknown command. Type /help for help.");
            } else {
                echo "Unknown command: $commandName
";
            }
            return;
        }

        try {
            $command->execute($sender, $parts);
        } catch (\Throwable $e) {
            Logger::error("Error executing command /$commandName: " . $e->getMessage());
            if ($sender instanceof Player) {
                $sender->sendMessage("An internal error occurred while executing this command.");
            }
        }
    }

    /**
     * @return array<string, Command>
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
