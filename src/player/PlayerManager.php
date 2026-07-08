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

declare (strict_types=1);

namespace watermossmc\player;

use watermossmc\mcpe\network\Session;
use watermossmc\Server;

final class PlayerManager
{
    /** @var Player[] */
    private static array $players = [];

    public static function add(Session $s, string $username, Server $server): Player
    {
        return self::$players[$s->getUuid()] = new Player($s, $username, $server);
    }

    public static function get(Session $s): ?Player
    {
        return self::$players[$s->getUuid()] ?? null;
    }

    public static function getByName(string $username): ?Player
    {
        foreach (self::$players as $player) {
            if (strcasecmp($player->username, $username) === 0) {
                return $player;
            }
        }
        return null;
    }

    public static function getByUuid(string $uuid): ?Player
    {
        return self::$players[$uuid] ?? null;
    }

    public static function remove(Session $s): void
    {
        $uuid = $s->getUuid();
        if (isset(self::$players[$uuid])) {
            unset(self::$players[$uuid]);
        }
    }

    /** @return Player[] */
    public static function all(): array
    {
        return self::$players;
    }

    public static function count(): int
    {
        return \count(self::$players);
    }
}
