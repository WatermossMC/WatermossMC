<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet;

use InvalidArgumentException;
use watermossmc\network\nethernet\webrtc\PeerConnection;
use watermossmc\util\Logger;

final class NetherNetTransport
{
    public function __construct(private readonly PeerConnection $peer)
    {
    }

    public function send(string $packet): void
    {
        try {
            foreach (MessageSegmenter::split($packet) as $segment) {
                if (!$this->peer->sendReliable($segment)) {
                    Logger::warning("[NetherNet] Reliable segment not queued (conn {$this->peer->connectionId}): channel closed or send buffer full");
                    return;
                }
            }
        } catch (InvalidArgumentException $e) {
            Logger::error('[NetherNet] ' . $e->getMessage());
        }
    }

    public function sendUnreliable(string $packet): void
    {
        try {
            $segments = MessageSegmenter::split($packet);
            if (\count($segments) > 1) {
                // The unreliable channel forbids fragmentation (the receiver rejects countdown > 0),
                // so oversized payloads must use the reliable channel instead of being dropped.
                $this->send($packet);
                return;
            }
            foreach ($segments as $segment) {
                if (!$this->peer->sendUnreliable($segment)) {
                    Logger::debug('[NetherNet] Unreliable segment was not queued');
                    return;
                }
            }
        } catch (InvalidArgumentException $e) {
            Logger::error('[NetherNet] ' . $e->getMessage());
        }
    }

    public function flush(): void
    {
        $this->peer->flush();
    }

    public function isOpen(): bool
    {
        return $this->peer->isOpen();
    }

    public function close(): void
  {
        $this->peer->closeGracefully('closed by server');
    }

    public function getConnectionId(): string
    {
        return $this->peer->connectionId;
    }
}
