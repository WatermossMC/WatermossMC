<?php

declare(strict_types=1);

namespace watermossmc\event;

use watermossmc\Server;
use watermossmc\world\World;

final class WorldLoadEvent extends Event
{
    public function __construct(
        public readonly Server $server,
        public readonly World $world
    ) {}
}
