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

namespace watermossmc\command\impl;

use watermossmc\command\Command;
use watermossmc\player\Player;
use watermossmc\Server;

final class GameModeCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'gamemode',
            'Changes the game mode of a player',
            '/gamemode <mode> [player]',
            \watermossmc\util\Permission::ROLE_OPERATOR
        );
    }

    /** @param array<string> $args */
    public function execute(mixed $sender, array $args): void
    {
        if (count($args) < 1) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }

        $mode = (int) $args[0];
        $targetName = $args[1] ?? null;

        if ($targetName === null) {
            if ($sender instanceof Player) {
                $targetName = $sender->getUsername();
            } else {
                $this->sendMessage($sender, "Please specify a player name.");
                return;
            }
        }

        $server = Server::getInstance();
        if ($server === null) {
            return;
        }

        $target = $server->getPlayer($targetName);
        if ($target === null) {
            $this->sendMessage($sender, "Player not found.");
            return;
        }

        $target->setGameMode($mode);
        $this->sendMessage($sender, "Game mode of {$target->getName()} set to {$mode}.");
    }
}
