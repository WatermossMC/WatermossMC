<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet;

use InvalidArgumentException;

final class MessageSegmenter
{
    public const MAX_SEGMENT_SIZE = 262143;

    public const MAX_SEGMENTS = 255;

    /** @return string[] */
    public static function split(string $message): array
    {
        if ($message === '') {
            return [];
        }
        $total = max(1, intdiv(\strlen($message) + self::MAX_SEGMENT_SIZE - 1, self::MAX_SEGMENT_SIZE));
        if ($total > self::MAX_SEGMENTS) {
            throw new InvalidArgumentException("Message too large for NetherNet: {$total} segments");
        }
        $out = [];
        for ($i = 0; $i < $total; $i++) {
            $out[] = \chr($total - 1 - $i) . substr($message, $i * self::MAX_SEGMENT_SIZE, self::MAX_SEGMENT_SIZE);
        }
        return $out;
    }
}
