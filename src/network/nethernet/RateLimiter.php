<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet;

/**
 * Fixed-window counter per key (typically a remote IP).
 * A limit <= 0 disables limiting for this instance.
 */
final class RateLimiter
{
    public const ALLOW = 0;
    /** Over the limit, first drop in this window: the caller should log it (once per window, to avoid log floods). */
    public const DROP_FIRST = 1;
    public const DROP = 2;

    /** @var array<string, array{start: float, count: int}> */
    private array $windows = [];

    public function __construct(
        private readonly int $limit,
        private readonly float $windowSeconds
    ) {
    }

    public function check(string $key, float $now): int
    {
        if ($this->limit <= 0) {
            return self::ALLOW;
        }
        $w = $this->windows[$key] ?? null;
        if ($w === null || $now - $w['start'] >= $this->windowSeconds) {
            $this->windows[$key] = ['start' => $now, 'count' => 1];
            return self::ALLOW;
        }
        $w['count']++;
        $this->windows[$key] = $w;
        if ($w['count'] <= $this->limit) {
            return self::ALLOW;
        }
        return $w['count'] === $this->limit + 1 ? self::DROP_FIRST : self::DROP;
    }

    /** Frees expired windows so the table cannot grow without bound. */
    public function prune(float $now): void
    {
        foreach ($this->windows as $key => $w) {
            if ($now - $w['start'] >= $this->windowSeconds) {
                unset($this->windows[$key]);
            }
        }
    }
}
