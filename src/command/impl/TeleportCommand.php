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

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\player\Player;
<<<<<<< HEAD
=======
use watermossmc\player\PlayerManager;
>>>>>>> 866a1c0 (...)

final class TeleportCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'tp',
            'Teleports a player',
            '/tp <player>'
        );
    }

    public function execute(mixed $sender, array $args): void
    {
        if (\count($args) < 1) {
            $this->sendMessage($sender, "Usage: /tp <player>");
            return;
        }

        $targetName = $args[0];
<<<<<<< HEAD
        $target = \watermossmc\player\PlayerManager::getByName($targetName);
=======
        $target = PlayerManager::getByName($targetName);
>>>>>>> 866a1c0 (...)

        if ($target === null) {
            $this->sendMessage($sender, "Player not found.");
            return;
        }

        if ($sender instanceof Player) {
            $sender->teleport(
                $target->x,
                $target->y,
                $target->z,
                $target->yaw,
                $target->pitch
            );
            $this->sendMessage($sender, "Teleported to {$target->getName()}.");
            $this->sendMessage($target, "You were teleported by " . $sender->getName() . ".");
        } else {
            $this->sendMessage($sender, "Console cannot teleport. Use /tp <player> <x> <y> <z>");
        }
    }
}
