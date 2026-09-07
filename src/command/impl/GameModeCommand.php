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
use watermossmc\command\CommandSender;
use watermossmc\player\Player;
use watermossmc\Server;
use watermossmc\util\Permission;

final class GameModeCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'gamemode',
            'Sets a player\'s game mode',
            '/gamemode <survival|creative|adventure> [player]',
            Permission::ROLE_OPERATOR,
            ['gm']
        );
    }

    /** @param array<string> $args */
    public function execute(CommandSender $sender, array $args): void
    {
        if (count($args) < 1) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }

        $modes = ['survival' => 0, 's' => 0, '0' => 0, 'creative' => 1, 'c' => 1, '1' => 1, 'adventure' => 2, 'a' => 2, '2' => 2];
        $modeName = strtolower($args[0]);
        if (!isset($modes[$modeName])) {
            $this->sendMessage($sender, 'Invalid game mode. Expected survival, creative, or adventure.');
            return;
        }
        $mode = $modes[$modeName];
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
        $names = ['survival', 'creative', 'adventure'];
        $this->sendMessage($sender, "Set {$target->getName()}'s game mode to {$names[$mode]}.");
    }
}
