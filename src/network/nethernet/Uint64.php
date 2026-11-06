<?php

declare(strict_types=1);

namespace watermossmc\network\nethernet;

final class Uint64
{
    public static function toLe(string $decimal): string
    {
        $decimal = ltrim($decimal, '0');
        if ($decimal === '') {
            $decimal = '0';
        }
        $out = '';
        for ($i = 0; $i < 8; $i++) {
            [$decimal, $rem] = self::divmod($decimal, 256);
            $out .= \chr($rem);
        }
        return $out;
    }

    public static function fromLe(string $bytes): string
    {
        $u = unpack('Pv', $bytes);
        return sprintf('%u', $u === false ? 0 : $u['v']);
    }

    public static function random(): string
    {
        return self::fromLe(random_bytes(8));
    }

    /** @return array{0: string, 1: int} */
    private static function divmod(string $decimal, int $divisor): array
    {
        $quotient = '';
        $r = 0;
        $len = \strlen($decimal);
        for ($i = 0; $i < $len; $i++) {
            $r = $r * 10 + (int) $decimal[$i];
            $quotient .= (string) intdiv($r, $divisor);
            $r %= $divisor;
        }
        $quotient = ltrim($quotient, '0');
        return [$quotient === '' ? '0' : $quotient, $r];
    }
}
