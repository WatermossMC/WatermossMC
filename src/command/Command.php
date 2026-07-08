<?php

<<<<<<< HEAD
=======
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

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\command;

use watermossmc\player\Player;
<<<<<<< HEAD
=======
use watermossmc\util\Permission;
>>>>>>> 866a1c0 (...)

abstract class Command
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $usage,
<<<<<<< HEAD
        public readonly int $permissionLevel = 0
=======
        public readonly int $requiredRole = Permission::ROLE_MEMBER
>>>>>>> 866a1c0 (...)
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
