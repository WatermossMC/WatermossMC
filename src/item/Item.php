<?php

declare(strict_types=1);

namespace watermossmc\item;

abstract class Item
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $type = 'generic'
    ) {}

    public function onUse(\watermossmc\player\Player $player): void
    {
        // Default: do nothing
    }

    public function onInteract(int $x, int $y, int $z, \watermossmc\player\Player $player): void
    {
        // Default: do nothing
    }
}
