<?php

declare(strict_types=1);

namespace watermossmc\event;

use watermossmc\player\Player;
use watermossmc\util\Location;

final class PlayerInteractEvent extends Event
{
    public function __construct(
        public readonly Player $player,
        public readonly Location $location,
        public readonly int $action // 0: Left, 1: Right
    ) {}
}
