<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft;

use WatermossMC\Network\Session;

final class PlayerManager
{
    /** @var Player[] */
    private static array $players = [];

    public static function add(Session $s, string $username): Player
    {
        return self::$players[$s->getUuid()] = new Player($s, $username);
    }

    public static function get(Session $s): ?Player
    {
        return self::$players[$s->getUuid()] ?? null;
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
}
