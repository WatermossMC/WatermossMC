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

final class FeedCommand extends Command
{
    public function __construct()
    {
        parent::__construct(
            'feed',
            'Restores a player\'s hunger level.',
            '/feed [player]',
            Permission::ROLE_OPERATOR
        );
    }

    /** @param array<string> $args */
    public function execute(CommandSender $sender, array $args): void
    {
        $server = Server::getInstance();
        if ($server === null) {
            return;
        }

        $target = null;
        if (isset($args[0])) {
            $target = $server->getPlayer($args[0]);
            if ($target === null) {
                $this->sendMessage($sender, 'That player cannot be found');
                return;
            }
        } else {
            if ($sender instanceof Player) {
                $target = $sender;
            } else {
                $this->sendMessage($sender, "Usage: {$this->usage}");
                return;
            }
        }

        $hungerAttr = $target->getAttributeMap()->get('minecraft:player.hunger');
        if ($hungerAttr !== null) {
            $hungerAttr->setValue($hungerAttr->getMax());
        }
        $saturationAttr = $target->getAttributeMap()->get('minecraft:player.saturation');
        if ($saturationAttr !== null) {
            $saturationAttr->setValue($saturationAttr->getMax());
        }

        $this->sendMessage($target, '§aYour hunger has been satisfied.');
        if ($sender !== $target) {
            $this->sendMessage($sender, '§aSuccessfully fed ' . $target->getName() . '.');
        }
    }
}
