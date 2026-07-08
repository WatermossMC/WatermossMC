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

declare (strict_types=1);

namespace watermossmc\world;

use RuntimeException;
use watermossmc\binary\Binary;

final class SubChunk
{
    public const SIZE = 4096;

    /** @var int[] */
    private array $blocks = [];

    public function __construct()
    {
        $this->blocks = array_fill(0, self::SIZE, 0);
    }

    public function setBlock(int $x, int $y, int $z, int $id): void
    {
        $index = $y << 8 | $z << 4 | $x;
        $this->blocks[$index] = $id;
    }

    public function getBlock(int $x, int $y, int $z): int
    {
        $index = $y << 8 | $z << 4 | $x;
        return $this->blocks[$index] ?? 0;
    }

    public function encode(): string
    {
        $out = Binary::writeByte(8);
        $palette = array_values(array_unique($this->blocks));
        $bits = max(1, (int) ceil(log(\count($palette), 2)));
        $out .= Binary::writeByte($bits);
        $out .= $this->encodeBlocks($palette, $bits);
        $out .= Binary::writeVarInt(\count($palette));
        foreach ($palette as $id) {
            $out .= Binary::writeVarInt($id);
        }
        return $out;
    }

    /**
     * @param array<int, int> $palette
     */
    private function encodeBlocks(array $palette, int $bits): string
    {
        $indexes = array_flip($palette);
        $buffer = '';
        $value = 0;
        $bitPos = 0;
        foreach ($this->blocks as $block) {
            $value |= $indexes[$block] << $bitPos;
            $bitPos += $bits;
            if ($bitPos >= 32) {
                $buffer .= Binary::writeInt($value);
                $value = 0;
                $bitPos = 0;
            }
        }
        if ($bitPos > 0) {
            $buffer .= Binary::writeInt($value);
        }
        return $buffer;
    }

    public function exportBinary(): string
    {
        return pack('v*', ...$this->blocks);
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
        $subChunk->blocks = array_values($values);
        return $subChunk;
    }
}
