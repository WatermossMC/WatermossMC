<?php

declare(strict_types=1);

namespace watermossmc\event;

use watermossmc\player\Player;
use watermossmc\util\Location;

final class PlayerMoveEvent extends Event
{
    public function __construct(
        public readonly Player $player,
        public readonly Location $from,
        public readonly Location $to
    ) {}
}
