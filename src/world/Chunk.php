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

final class Chunk
{
    public int $x;

    public int $z;

    /** @var SubChunk[] */
    private array $subChunks = [];

    public function __construct(int $x, int $z)
    {
        $this->x = $x;
        $this->z = $z;
    }

    public function setBlock(int $x, int $y, int $z, int $id): void
    {
        $subY = intdiv($y, 16);
        $localY = $y & 0x0F;

        if (!isset($this->subChunks[$subY])) {
            $this->subChunks[$subY] = new SubChunk();
        }

        $this->subChunks[$subY]->setBlock($x, $localY, $z, $id);
    }

    public function getBlock(int $x, int $y, int $z): int
    {
        $subY = intdiv($y, 16);
        $localY = $y & 0x0F;

        if (!isset($this->subChunks[$subY])) {
            return 0;
        }

        return $this->subChunks[$subY]->getBlock($x, $localY, $z);
    }

    /**
     * Encode chunk to network payload
     */
    public function encode(): string
    {
        $payload = '';

        ksort($this->subChunks);

        foreach ($this->subChunks as $subChunk) {
            $payload .= $subChunk->encode();
        }

        return $payload;
    }

    public function getSubChunkCount(): int
    {
        return \count($this->subChunks);
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
