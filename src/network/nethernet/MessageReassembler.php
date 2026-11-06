<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet;

final class MessageReassembler
{
    private const MAX_BUFFER = 262_143 * 255;

    private string $buffer = '';

    private int $remaining = 0;

    public function __construct(
        private readonly bool $fragmentationAllowed = true,
        private readonly int $maxPayloadSize = self::MAX_BUFFER
    ) {
    }

    private function reset(): void
    {
        $this->buffer = '';
        $this->remaining = 0;
    }

    /**
     * Feeds one segment; returns the complete message when complete (remaining = 0), else null.
     *
     * @throws NetherNetException on malformed framing (the peer is misbehaving, so the caller must react)
     */
    public function push(string $segment): ?string
    {
        $length = \strlen($segment);
        if ($length < 2) {
            $this->reset();
            throw new NetherNetException("Segment must have a counter byte and at least one payload byte, got {$length} bytes");
        }

        $remaining = \ord($segment[0]);
        if (!$this->fragmentationAllowed && $remaining !== 0) {
            $this->reset();
            throw new NetherNetException("Channel does not support fragmentation, got remaining count {$remaining}");
        }

        if ($this->remaining > 0 && $remaining !== $this->remaining - 1) {
            $expected = $this->remaining - 1;
            $this->reset();
            throw new NetherNetException("Expected segment countdown {$expected}, got {$remaining}");
        }

        if (\strlen($this->buffer) + $length - 1 > $this->maxPayloadSize) {
            $this->reset();
            throw new NetherNetException("Assembled payload would exceed the {$this->maxPayloadSize} byte limit");
        }

        $this->buffer .= substr($segment, 1);
        $this->remaining = $remaining;

        if ($remaining !== 0) {
            return null;
        }

        $payload = $this->buffer;
        $this->reset();

        return $payload;
    }
}
