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

abstract class Command
{
    /** @var list<string> */
    public readonly array $aliases;

    /**
     * @param list<string> $aliases Alternate names that execute this command.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $usage,
        public readonly int $requiredRole = Permission::ROLE_MEMBER,
        array $aliases = [],
    ) {
        $this->aliases = array_values(array_unique(array_map(static fn (string $alias): string => strtolower(trim($alias)), $aliases)));
    }

    /** @return list<string> */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param array<string> $args
     */
    abstract public function execute(CommandSender $sender, array $args): void;

    protected function sendMessage(CommandSender $sender, string $message): void
    {
        $sender->sendMessage($message);
    }
}
