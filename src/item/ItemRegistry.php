<?php

declare(strict_types=1);

namespace watermossmc\item;

final class ItemRegistry
{
    /** @var array<int, Item> */
    private static array $items = [];

    public static function register(Item $item): void
    {
        self::$items[$item->id] = $item;
    }

    public static function get(int $id): ?Item
    {
        return self::$items[$id] ?? null;
    }

    /**
     * @return array<int, Item>
     */
    public static function all(): array
    {
        return self::$items;
    }
}
