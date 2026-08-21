<?php

declare(strict_types=1);

namespace watermossmc\mcpe\protocol;

use Closure;
use InvalidArgumentException;
use Socket;
use watermossmc\mcpe\network\Session;

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
