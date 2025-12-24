<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft\World;

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
}
