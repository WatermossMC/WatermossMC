<?php

declare(strict_types=1);

namespace watermossmc\event;

use watermossmc\Server;

final class ServerStartEvent extends Event
{
    public function __construct(public readonly Server $server) {}
}
