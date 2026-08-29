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

namespace watermossmc\world;

use RuntimeException;
use SplFixedArray;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\block\BlockRegistry;
use watermossmc\block\BlockRuntimeIdConverter;

final class SubChunk
{
    public const SIZE = 4096;
    public const EDGE_LENGTH = 16;
    public const COORD_BIT_SIZE = 4;
    public const COORD_MASK = 0x0F;

    /** @var SplFixedArray<int> */
    private SplFixedArray $blocks;

    private int $emptyBlockId;

    public function __construct(int $emptyBlockId = 0)
    {
        $this->emptyBlockId = $emptyBlockId;
        $this->blocks = new SplFixedArray(self::SIZE);
        for ($i = 0; $i < self::SIZE; $i++) {
            $this->blocks[$i] = $emptyBlockId;
        }
    }

    public function getEmptyBlockId(): int
    {
        return $this->emptyBlockId;
    }

    public function setBlock(int $x, int $y, int $z, int $id): void
    {
        $index = ($x & 0x0f) << 8 | ($z & 0x0f) << 4 | ($y & 0x0f);
        $this->blocks[$index] = $id;
    }

    public function getBlock(int $x, int $y, int $z): int
    {
        $index = ($x & 0x0f) << 8 | ($z & 0x0f) << 4 | ($y & 0x0f);
        return $this->blocks[$index] ?? $this->emptyBlockId;
    }

    public function isEmptyFast(): bool
    {
        foreach ($this->blocks as $block) {
            if ($block !== $this->emptyBlockId) {
                return false;
            }
        }
        return true;
    }

    public function isEmptyAuthoritative(): bool
    {
        return $this->isEmptyFast();
    }

    public function encode(BlockRuntimeIdConverter $converter): string
    {
        $palette = [$this->emptyBlockId => 0];
        $runtimeIdMap = [];
        $indexes = new SplFixedArray(self::SIZE);

        $defaultBlock = BlockRegistry::get($this->emptyBlockId);
        $defaultRuntimeId = $defaultBlock !== null ? $converter->toRuntimeId($defaultBlock) : 0;
        $palette[0] = $defaultRuntimeId;
        $runtimeIdMap[$defaultRuntimeId] = 0;

        foreach ($this->blocks as $i => $blockStateId) {
            $block = BlockRegistry::get((int) $blockStateId);
            if ($block === null) {
                $indexes[$i] = 0;
                continue;
            }
            $runtimeId = $converter->toRuntimeId($block);
            if (!isset($runtimeIdMap[$runtimeId])) {
                $runtimeIdMap[$runtimeId] = count($palette);
                $palette[] = $runtimeId;
            }
            $indexes[$i] = $runtimeIdMap[$runtimeId];
        }

        $paletteCount = count($palette);
        $bits = 1;
        foreach ([1, 2, 3, 4, 5, 6, 8, 16] as $b) {
            if ((1 << $b) >= $paletteCount) {
                $bits = $b;
                break;
            }
        }
        if ($bits > 8) {
            $bits = 16;
        }

        $out = '';
        $out .= Binary::writeByte(8); // version 8
        $out .= Binary::writeByte(1); // storage count (1 layer)
        $out .= Binary::writeByte($bits << 1);

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
            $out .= Binary::writeInt($word);
        }

        $out .= McpeBinary::writeUnsignedVarInt($paletteCount);
        foreach ($palette as $runtimeId) {
            $out .= McpeBinary::writeUnsignedVarInt($runtimeId);
        }

        return $out;
    }

    public function exportBinary(): string
    {
        return pack('v*', ...$this->blocks->toArray());
    }

    public static function fromBinary(string $data): self
    {
        if (\strlen($data) !== self::SIZE * 2) {
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
        return $this->blocks;
    }
}
