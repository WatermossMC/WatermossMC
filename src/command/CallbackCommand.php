<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

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
