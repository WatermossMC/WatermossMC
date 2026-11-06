<?php

declare(strict_types=1);

namespace watermossmc\world;

use RuntimeException;
use SplFixedArray;
use watermossmc\binary\McpeBinary;
use watermossmc\block\BlockRegistry;
use watermossmc\block\BlockRuntimeIdConverter;

final class SubChunk
{
    public const SIZE = 4096;
    public const EDGE_LENGTH = 16;
    public const COORD_BIT_SIZE = 4;
    public const COORD_MASK = 0x0F;

    /** @var SplFixedArray<int>|null */
    private ?SplFixedArray $blocks = null;

    private int $emptyBlockId;

    public function __construct(int $emptyBlockId = 0)
    {
        $this->emptyBlockId = $emptyBlockId;
    }

    private function allocateBlocks(): void
    {
        if ($this->blocks !== null) {
            return;
        }

        $this->blocks = new SplFixedArray(self::SIZE);

        for ($i = 0; $i < self::SIZE; $i++) {
            $this->blocks[$i] = $this->emptyBlockId;
        }
    }

    public function getEmptyBlockId(): int
    {
        return $this->emptyBlockId;
    }

    public function setBlock(int $x, int $y, int $z, int $id): void
    {
        if ($this->blocks === null && $id === $this->emptyBlockId) {
            return;
        }

        $this->allocateBlocks();

        $index = (($x & 0x0F) << 8)
            | (($z & 0x0F) << 4)
            | ($y & 0x0F);

        $this->blocks[$index] = $id;
    }

    public function getBlock(int $x, int $y, int $z): int
    {
        if ($this->blocks === null) {
            return $this->emptyBlockId;
        }

        $index = (($x & 0x0F) << 8)
            | (($z & 0x0F) << 4)
            | ($y & 0x0F);

        return $this->blocks[$index] ?? $this->emptyBlockId;
    }

    public function isEmptyFast(): bool
    {
        if ($this->blocks === null) {
            return true;
        }

        for ($i = 0; $i < self::SIZE; $i++) {
            if ($this->blocks[$i] !== $this->emptyBlockId) {
                return false;
            }
        }

        return true;
    }

    public function isEmptyAuthoritative(): bool
    {
        return $this->isEmptyFast();
    }

    public static function encodeEmpty(
        BlockRuntimeIdConverter $converter,
        int $emptyBlockId = 0
    ): string {
        $defaultBlock = BlockRegistry::get($emptyBlockId);

        $runtimeId = $defaultBlock !== null
            ? $converter->toRuntimeId($defaultBlock)
            : 0;

        return "\x08\x01"
            . "\x02"
            . str_repeat("\x00", 512)
            . McpeBinary::writeUnsignedVarInt(1)
            . McpeBinary::writeUnsignedVarInt($runtimeId);
    }

    public function encode(BlockRuntimeIdConverter $converter): string
    {
        if ($this->blocks === null || $this->isEmptyFast()) {
            return self::encodeEmpty($converter, $this->emptyBlockId);
        }

        $palette = [];
        $runtimeIdMap = [];
        $indexes = new SplFixedArray(self::SIZE);

        $defaultBlock = BlockRegistry::get($this->emptyBlockId);

        $defaultRuntimeId = $defaultBlock !== null
            ? $converter->toRuntimeId($defaultBlock)
            : 0;

        $palette[] = $defaultRuntimeId;
        $runtimeIdMap[$defaultRuntimeId] = 0;

        for ($i = 0; $i < self::SIZE; $i++) {
            $blockStateId = (int) $this->blocks[$i];
            $block = BlockRegistry::get($blockStateId);

            $runtimeId = $block !== null
                ? $converter->toRuntimeId($block)
                : $defaultRuntimeId;

            $paletteIndex = $runtimeIdMap[$runtimeId] ?? null;

            if ($paletteIndex === null) {
                $paletteIndex = count($palette);
                $runtimeIdMap[$runtimeId] = $paletteIndex;
                $palette[] = $runtimeId;
            }

            $indexes[$i] = $paletteIndex;
        }

        $paletteCount = count($palette);
        $bits = 1;

        foreach ([1, 2, 3, 4, 5, 6, 8, 16] as $b) {
            if ((1 << $b) >= $paletteCount) {
                $bits = $b;
                break;
            }
        }

        $out = "\x08\x01" . chr($bits << 1);

        $words = (int) ceil((self::SIZE * $bits) / 32);
        $wordArray = array_fill(0, $words, 0);

        for ($i = 0; $i < self::SIZE; $i++) {
            $val = $indexes[$i];
            $bitOffset = $i * $bits;
            $wordIndex = $bitOffset >> 5;
            $bitInWord = $bitOffset & 31;

            $wordArray[$wordIndex] |= ($val << $bitInWord);
        }

        foreach ($wordArray as $word) {
            $out .= pack('N', $word);
        }

        $out .= McpeBinary::writeUnsignedVarInt($paletteCount);

        foreach ($palette as $runtimeId) {
            $out .= McpeBinary::writeUnsignedVarInt($runtimeId);
        }

        return $out;
    }

    public function exportBinary(): string
    {
        if ($this->blocks === null) {
            return str_repeat(pack('v', $this->emptyBlockId), self::SIZE);
        }

        $buffer = '';

        for ($i = 0; $i < self::SIZE; $i++) {
            $buffer .= pack('v', (int) $this->blocks[$i]);
        }

        return $buffer;
    }

    public static function fromBinary(string $data): self
    {
        if (strlen($data) !== self::SIZE * 2) {
            throw new RuntimeException('Invalid subchunk binary size');
        }

        $values = unpack('v' . self::SIZE, $data);

        if ($values === false) {
            throw new RuntimeException('Failed to decode subchunk binary');
        }

        $subChunk = new self();
        $subChunk->blocks = SplFixedArray::fromArray(array_values($values), false);

        return $subChunk;
    }

    /**
     * @return SplFixedArray<int>
     */
    public function getBlocks(): SplFixedArray
    {
        $this->allocateBlocks();

        return $this->blocks;
    }
}