<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\protocol;

use Closure;
use InvalidArgumentException;
use Socket;
use watermossmc\network\Session;

/**
 * Extensible router for packets received from a Bedrock client.
 *
 * A handler receives the complete packet, an offset positioned immediately
 * after its packet ID, the session, and the UDP socket. It must return true
 * when it has handled the packet.
 */
final class InboundPacketRegistry
{
    /** @var array<int, Closure(string, int, Session, Socket): bool> */
    private array $handlers = [];

    /** @param callable(string, int, Session, Socket): bool $handler */
    public function register(int $packetId, callable $handler, bool $replace = false): void
    {
        if (isset($this->handlers[$packetId]) && !$replace) {
            throw new InvalidArgumentException("A handler is already registered for packet ID {$packetId}.");
        }

        $this->handlers[$packetId] = Closure::fromCallable($handler);
    }

    public function dispatch(int $packetId, string $packet, int $offset, Session $session, Socket $socket): bool
    {
        $handler = $this->handlers[$packetId] ?? null;

        return $handler !== null && $handler($packet, $offset, $session, $socket);
    }

    public function has(int $packetId): bool
    {
        return isset($this->handlers[$packetId]);
    }
}
