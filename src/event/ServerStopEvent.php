<?php

declare(strict_types=1);

namespace watermossmc\event;

use watermossmc\Server;

final class ServerStopEvent extends Event
{
    public function __construct(public readonly Server $server) {}
}
