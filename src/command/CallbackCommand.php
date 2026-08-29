<?php

declare(strict_types=1);

namespace watermossmc\command;

use watermossmc\util\Permission;

/**
 * A command backed by a closure, intended for concise plugin commands.
 */
final class CallbackCommand extends Command
{
    /** @var callable(CommandSender, array<string>): void */
    private $handler;

    /**
     * @param callable(CommandSender, array<string>): void $handler
     * @param list<string> $aliases
     */
    public function __construct(string $name, string $description, string $usage, callable $handler, int $requiredRole = Permission::ROLE_MEMBER, array $aliases = [])
    {
        parent::__construct($name, $description, $usage, $requiredRole, $aliases);
        $this->handler = $handler;
    }

    /** @param array<string> $args */
    public function execute(CommandSender $sender, array $args): void
    {
        ($this->handler)($sender, $args);
    }
}
