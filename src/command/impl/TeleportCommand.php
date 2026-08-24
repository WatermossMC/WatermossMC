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
use watermossmc\player\PlayerManager;

final class TeleportCommand extends Command
{
    public function __construct()
    {
        parent::__construct('tp', 'Teleports entities', '/tp [player] <destination|x y z>', aliases: ['teleport']);
    }

    public function execute(mixed $sender, array $args): void
    {
        if (\count($args) < 1) {
            $this->sendMessage($sender, "Usage: {$this->usage}");
            return;
        }
        if (count($args) === 3 && $sender instanceof Player) {
            $coordinates = $this->parseCoordinates($sender, $args);
            if ($coordinates === null) {
                $this->sendMessage($sender, 'Invalid coordinates.');
                return;
            }
            [$x, $y, $z] = $coordinates;
            $sender->teleport($x, $y, $z);
            $this->sendMessage($sender, 'Teleported ' . $sender->getName() . ' to ' . $x . ', ' . $y . ', ' . $z . '.');
            return;
        }
        $target = PlayerManager::getByName($args[0]);
        if ($target === null) {
            $this->sendMessage($sender, 'No player was found.');
            return;
        }
        if (count($args) === 1 && $sender instanceof Player) {
            $sender->teleport($target->x, $target->y, $target->z, $target->yaw, $target->pitch);
            $this->sendMessage($sender, 'Teleported ' . $sender->getName() . ' to ' . $target->getName() . '.');
            return;
        }
        if (count($args) === 4) {
            $coordinates = $this->parseCoordinates($target, array_slice($args, 1));
            if ($coordinates === null) {
                $this->sendMessage($sender, 'Invalid coordinates.');
                return;
            }
            [$x, $y, $z] = $coordinates;
            $target->teleport($x, $y, $z);
            $this->sendMessage($sender, 'Teleported ' . $target->getName() . ' to ' . $x . ', ' . $y . ', ' . $z . '.');
            return;
        }
        $this->sendMessage($sender, "Usage: {$this->usage}");
    }

    /** @param list<string> $values @return array{float, float, float}|null */
    private function parseCoordinates(Player $origin, array $values): ?array
    {
        $base = [$origin->x, $origin->y, $origin->z];
        $coordinates = [];
        foreach ($values as $index => $value) {
            if (str_starts_with($value, '~')) {
                $offset = substr($value, 1);
                if ($offset !== '' && !is_numeric($offset)) {
                    return null;
                }
                $coordinates[] = $base[$index] + ($offset === '' ? 0.0 : (float) $offset);
            } elseif (is_numeric($value)) {
                $coordinates[] = (float) $value;
            } else {
                return null;
            }
        }
        return $coordinates;
    }
}
