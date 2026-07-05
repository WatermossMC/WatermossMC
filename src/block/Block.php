<?php

declare(strict_types=1);

namespace watermossmc\block;

abstract class Block
{
    public const AIR = 0;
    public const STONE = 1;
    public const GRASS = 2;
    public const DIRT = 3;
    public const BEDROCK = 7;

    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}

    public function onPlace(int $x, int $y, int $z): void
    {
        // Default: do nothing
    }

    public function onBreak(int $x, int $y, int $z): void
    {
        // Default: do nothing
    }

    public function onInteract(int $x, int $y, int $z, \watermossmc\player\Player $player): void
    {
        // Default: do nothing
    }
}
