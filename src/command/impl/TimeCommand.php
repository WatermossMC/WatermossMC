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
use watermossmc\Server;
use watermossmc\util\Permission;

final class TimeCommand extends Command
{
    private const TIME_SPECS = ['day' => 1000, 'sunrise' => 23000, 'noon' => 6000, 'sunset' => 12000, 'night' => 13000, 'midnight' => 18000];

    public function __construct()
    {
        parent::__construct('time', 'Changes or queries the world time', '/time <set|add|query> <value>', Permission::ROLE_OPERATOR);
    }

    public function execute(CommandSender $sender, array $args): void
    {
        $world = Server::getInstance()?->getWorld();
        if ($world === null || $args === []) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        $action = strtolower($args[0]);
        if ($action === 'query') {
            $query = strtolower($args[1] ?? 'daytime');
            $value = match ($query) {
                'daytime' => $world->getDayTime(),
                'gametime' => $world->getTime(),
                'day' => intdiv($world->getTime(), 24000),
                default => null,
            };
            if ($value === null) {
                $this->sendMessage($sender, 'Unknown time query. Expected daytime, gametime, or day.');
                return;
            }
            $this->sendMessage($sender, 'The time is ' . $value . '.');
            return;
        }
        $value = $args[1] ?? null;
        $amount = $value !== null ? self::TIME_SPECS[strtolower($value)] ?? (is_numeric($value) ? (int) $value : null) : null;
        if ($amount === null || !in_array($action, ['set', 'add'], true)) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        if ($action === 'set') {
            $world->setDayTime($amount);
            $this->sendMessage($sender, 'Set the time to ' . $amount . '.');
            return;
        }
        $world->setTime($world->getTime() + $amount);
        $this->sendMessage($sender, 'Added ' . $amount . ' to the time.');
    }
}
