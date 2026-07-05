<?php

declare(strict_types=1);

namespace watermossmc\block;

final class BlockRegistry
{
    /** @var array<int, Block> */
    private static array $blocks = [];

    public static function register(Block $block): void
    {
        self::$blocks[$block->id] = $block;
    }

    public static function get(int $id): ?Block
    {
        return self::$blocks[$id] ?? null;
    }

    /**
     * @return array<int, Block>
     */
    public static function all(): array
    {
        return self::$blocks;
    }
}
