<?php

declare(strict_types=1);

namespace watermossmc\event;

use watermossmc\player\Player;
use watermossmc\Server;

final class PlayerJoinEvent extends Event
{
    public function __construct(
        public readonly Server $server,
        public readonly Player $player
    ) {}
}
