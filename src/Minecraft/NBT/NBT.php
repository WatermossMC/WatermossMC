<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\NBT;

final class NBT
{
    public const TAG_END = 0;
    public const TAG_BYTE = 1;
    public const TAG_SHORT = 2;
    public const TAG_INT = 3;
    public const TAG_LONG = 4;
    public const TAG_FLOAT = 5;
    public const TAG_DOUBLE = 6;
    public const TAG_BYTE_ARRAY = 7;
    public const TAG_STRING = 8;
    public const TAG_LIST = 9;
    public const TAG_COMPOUND = 10;
    public const TAG_INT_ARRAY = 11;
    public const TAG_LONG_ARRAY = 12;

    /**
     * @param array<string, mixed> $data
     */
    public static function compound(array $data): string
    {
        $buf = \chr(self::TAG_COMPOUND);
        $buf .= self::writeString('');

        foreach ($data as $name => $value) {
            $buf .= self::writeNamedTag($name, $value);
        }

        return $buf . \chr(self::TAG_END);
    }

    private static function writeNamedTag(string $name, mixed $value): string
    {
        [$tag, $payload] = self::detectTag($value);

        return \chr($tag)
            . self::writeString($name)
            . $payload;
    }

    /**
     * @return array{0:int,1:string}
     */
    private static function detectTag(mixed $value): array
    {
        if (\is_int($value)) {
            return [self::TAG_INT, self::writeInt($value)];
        }

        if (\is_float($value)) {
            return [self::TAG_FLOAT, self::writeFloat($value)];
        }

        if (\is_string($value)) {
            return [self::TAG_STRING, self::writeString($value)];
        }

        if (\is_bool($value)) {
            return [self::TAG_BYTE, \chr($value ? 1 : 0)];
        }

        if (\is_array($value)) {
            if (self::isList($value)) {
                return self::writeList($value);
            }

            return [self::TAG_COMPOUND, self::writeCompoundPayload($value)];
        }

        throw new \RuntimeException('Unsupported NBT type');
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function writeCompoundPayload(array $data): string
    {
        $buf = '';

        foreach ($data as $name => $value) {
            $buf .= self::writeNamedTag($name, $value);
        }

        return $buf . \chr(self::TAG_END);
    }

    /**
     * @param array<int, mixed> $list
     * @return array{0:int,1:string}
     */
    private static function writeList(array $list): array
    {
        if ($list === []) {
            return [
                self::TAG_LIST,
                \chr(self::TAG_END) . pack('V', 0),
            ];
        }

        [$childTag] = self::detectTag($list[0]);

        $buf = \chr($childTag);
        $buf .= pack('V', \count($list));

        foreach ($list as $value) {
            [, $payload] = self::detectTag($value);
            $buf .= $payload;
        }

        return [self::TAG_LIST, $buf];
    }

    private static function writeString(string $v): string
    {
        return pack('v', \strlen($v)) . $v;
    }

    private static function writeInt(int $v): string
    {
        return pack('V', $v);
    }

    private static function writeFloat(float $v): string
    {
        return pack('g', $v);
    }

    /**
     * @param array<mixed> $arr
     */
    private static function isList(array $arr): bool
    {
        return array_keys($arr) === range(0, \count($arr) - 1);
    }
}
