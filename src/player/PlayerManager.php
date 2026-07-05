<?php

declare(strict_types=1);

namespace watermossmc\player;

use watermossmc\mcpe\network\Session;

final class PlayerManager
{
    /** @var Player[] */
    private static array $players = [];

    public static function add(Session $s, string $username, \watermossmc\Server $server): Player
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
