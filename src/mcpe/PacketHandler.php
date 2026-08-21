<?php

declare(strict_types=1);

namespace watermossmc\mcpe;

use Socket;
use watermossmc\mcpe\network\Session;

interface PacketHandler
{
    /**
     * @return int[]
     */
    public function packetIds(): array;

    public function handle(string $packet, int $offset, Session $session, Socket $socket): bool;
}
