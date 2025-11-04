<?php

namespace WatermossMC\Command;

use WatermossMC\Server;
// Pastikan kelas-kelas command yang digunakan di-import jika berada di namespace lain
// use WatermossMC\Command\StopCommand; 
// use WatermossMC\Command\HelpCommand;

/**
 * @property array<string, Command> $commands
 * Tipe array spesifik dideklarasikan melalui PHPDoc agar PHPStan tahu bahwa
 * array ini berisi objek Command yang di-indeks oleh string (nama command).
 */
class CommandManager
{
    private Server $server;

    /**
     * @var Command[]|array<string, Command>
     * Kita menggunakan sintaks array dasar 'array' untuk PHP, dan PHPDoc
     * untuk memberikan detail lebih lanjut kepada PHPStan.
     */
    private array $commands = []; // Menambahkan 'array' sebagai tipe (PHP 7.4+)

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->registerDefaultCommands();
    }

    public function registerCommand(Command $command): void
    {
        // Karena CommandManager tahu bahwa $command adalah instance dari Command,
        // dan getName() mengembalikan string, penambahan ke array ini aman.
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
