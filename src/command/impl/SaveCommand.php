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
use watermossmc\player\OperatorManager;
use watermossmc\Server;
use watermossmc\util\Permission;

final class SaveCommand extends Command
{
    private static bool $savingEnabled = true;

    public function __construct()
    {
        parent::__construct(
            'save',
            'Control or check how the game saves data to disk.',
            '/save <all|on|off>',
            Permission::ROLE_OPERATOR,
            ['save-all', 'save-on', 'save-off']
        );
    }

    /** @param array<string> $args */
    public function execute(CommandSender $sender, array $args): void
    {
        $server = Server::getInstance();
        if ($server === null) {
            return;
        }

        $commandLabel = strtolower($this->name);
        if (isset($args[0])) {
            $sub = strtolower($args[0]);
            if ($sub === 'all') {
                $commandLabel = 'save-all';
            } elseif ($sub === 'on') {
                $commandLabel = 'save-on';
            } elseif ($sub === 'off') {
                $commandLabel = 'save-off';
            }
        }

        switch ($commandLabel) {
            case 'save-all':
            case 'all':
                $this->sendMessage($sender, 'Saving...');
                $server->saveWorld();
                OperatorManager::save($server);
                $this->sendMessage($sender, 'Saved the world');
                $this->sendMessage($sender, 'Data saved. Files are now ready to be copied.');
                break;

            case 'save-on':
            case 'on':
                if (self::$savingEnabled) {
                    $this->sendMessage($sender, 'Saving is already turned on.');
                    return;
                }
                self::$savingEnabled = true;
                $this->sendMessage($sender, 'Turned on world auto-saving');
                $this->sendMessage($sender, 'Changes to the world are resumed.');
                break;

            case 'save-off':
            case 'off':
                if (!self::$savingEnabled) {
                    $this->sendMessage($sender, 'Saving is already turned off.');
                    return;
                }
                self::$savingEnabled = false;
                $this->sendMessage($sender, 'Turned off world auto-saving');
                break;

            default:
                $this->sendMessage($sender, "Usage: {$this->usage}");
                break;
        }
    }

    public static function isSavingEnabled(): bool
    {
        return self::$savingEnabled;
    }
}
