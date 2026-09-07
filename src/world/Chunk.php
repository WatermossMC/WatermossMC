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

use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\block\BlockRuntimeIdConverter;

final class Chunk
{
    public const MIN_SUBCHUNK_INDEX = -4;
    public const MAX_SUBCHUNK_INDEX = 19;
    public const EDGE_LENGTH = 16;
    public const COORD_BIT_SIZE = 4;
    public const COORD_MASK = 0x0F;

    public int $x;

    public int $z;

    /** @var array<int, SubChunk> */
    private array $subChunks = [];

    public function __construct(int $x, int $z)
    {
        $this->x = $x;
        $this->z = $z;
        for ($y = self::MIN_SUBCHUNK_INDEX; $y <= self::MAX_SUBCHUNK_INDEX; $y++) {
            $this->subChunks[$y] = new SubChunk();
        }
    }

    public function setBlock(int $x, int $y, int $z, int $id): void
    {
        $subY = $y >> 4;
        $localY = $y & 0x0F;

        if (!isset($this->subChunks[$subY])) {
            $this->subChunks[$subY] = new SubChunk();
        }

        $this->subChunks[$subY]->setBlock($x, $localY, $z, $id);
    }

    public function getBlock(int $x, int $y, int $z): int
    {
        $subY = $y >> 4;
        $localY = $y & 0x0F;

        if (!isset($this->subChunks[$subY])) {
            return 0;
        }

        return $this->subChunks[$subY]->getBlock($x, $localY, $z);
    }

    public function getSubChunk(int $y): SubChunk
    {
        if (!isset($this->subChunks[$y])) {
            $this->subChunks[$y] = new SubChunk();
        }
        return $this->subChunks[$y];
    }

    public function setSubChunk(int $y, SubChunk $subChunk): void
    {
        $this->subChunks[$y] = $subChunk;
    }

    /**
     * @return array<int, SubChunk>
     */
    public function getSubChunks(): array
    {
        return $this->subChunks;
    }

    /**
     * Encode chunk to network payload
     */
    public function encode(BlockRuntimeIdConverter $converter): string
    {
        $payload = '';

        ksort($this->subChunks);

        foreach ($this->subChunks as $subChunk) {
            $payload .= $subChunk->encode($converter);
        }

        // Biome data (256 bytes per column or similar simplistic palette/array)
        $payload .= str_repeat("\x01\x00\x00\x00", 256); // 256 entries of Biome ID 1 (Plains)

        // Border blocks data count (0)
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        return $payload;
    }

    public function getSubChunkCount(): int
    {
        $count = 0;
        for ($y = self::MAX_SUBCHUNK_INDEX; $y >= self::MIN_SUBCHUNK_INDEX; --$y) {
            if (isset($this->subChunks[$y]) && !$this->subChunks[$y]->isEmptyFast()) {
                $count = $y - self::MIN_SUBCHUNK_INDEX + 1;
                break;
            }
        }
        return max(1, $count);
    }

    public function exportBinary(): string
    {
        $buffer = Binary::writeLInt(\count($this->subChunks));

        foreach ($this->subChunks as $subY => $subChunk) {
            $buffer .= Binary::writeLInt($subY);
            $buffer .= $subChunk->exportBinary();
        }

        return $buffer;
    }

    public static function fromBinary(int $x, int $z, string $data): self
    {
        $chunk = new self($x, $z);
        $offset = 0;

        $subChunkCount = Binary::readLInt($data, $offset);

        for ($i = 0; $i < $subChunkCount; $i++) {
            $subY = Binary::readLInt($data, $offset);
            $subChunkData = substr($data, $offset, SubChunk::SIZE * 2);
            $offset += SubChunk::SIZE * 2;
            $chunk->subChunks[$subY] = SubChunk::fromBinary($subChunkData);
        }

        return $chunk;
    }
}
