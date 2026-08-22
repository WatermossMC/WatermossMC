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

final class StopCommand extends Command
{
    public function __construct()
    {
        parent::__construct('stop', 'Stops the server', '/stop');
    }

    public function execute(mixed $sender, array $args): void
    {
        if ($sender instanceof Player) {
            $this->sendMessage($sender, "You do not have permission to stop the server.");
            return;
        }
        $this->sendMessage($sender, "Stopping server...");
        $server = Server::getInstance();
        if ($server !== null) {
            $server->shutdown();
        }
    }
}
