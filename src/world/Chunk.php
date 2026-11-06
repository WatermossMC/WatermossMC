<?php

/*
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
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

    public function encode(BlockRuntimeIdConverter $converter): string
    {
        $payload = '';

        $indexes = array_unique([
            ...range(self::MIN_SUBCHUNK_INDEX, self::MAX_SUBCHUNK_INDEX),
            ...array_keys($this->subChunks)
        ]);

        sort($indexes, SORT_NUMERIC);

        foreach ($indexes as $y) {
            $subChunk = $this->subChunks[$y] ?? null;

            if ($subChunk === null) {
                $payload .= SubChunk::encodeEmpty($converter);
                continue;
            }

            $payload .= $subChunk->encode($converter);
        }

        $payload .= str_repeat("\x01\x00\x00\x00", 256);
        $payload .= McpeBinary::writeUnsignedVarInt(0);

        return $payload;
    }

    public function getSubChunkCount(): int
    {
        for ($y = self::MAX_SUBCHUNK_INDEX; $y >= self::MIN_SUBCHUNK_INDEX; --$y) {
            if (isset($this->subChunks[$y]) && !$this->subChunks[$y]->isEmptyFast()) {
                return $y - self::MIN_SUBCHUNK_INDEX + 1;
            }
        }

        return 1;
    }

    public function exportBinary(): string
    {
        $buffer = Binary::writeLInt(count($this->subChunks));

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