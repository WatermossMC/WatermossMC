<?php

declare(strict_types=1);

namespace watermossmc\command;

use watermossmc\player\Player;

abstract class Command
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $usage,
        public readonly int $permissionLevel = 0
    ) {}

    /**
     * @param Player|null $sender Null if the command was executed from console.
     * @param array<string> $args
     */
    public function execute(mixed $sender, array $args): void {}

    protected function sendMessage(mixed $sender, string $message): void
    {
        if ($sender instanceof Player) {
            $sender->sendMessage($message);
        } else {
            echo "[$sender] $message
";
        }
    }
}
