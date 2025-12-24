<?php


declare(strict_types=1);

namespace WatermossMC\Minecraft;

use WatermossMC\Minecraft\World\Chunk;

final class World
{
    public string $name;

    public int $seed;

    /** @var array<string, Chunk> */
    private array $chunks = [];

    public function __construct(string $name = 'world', int $seed = 0)
    {
        $this->name = $name;
        $this->seed = $seed;
    }

    public function getChunk(int $x, int $z): Chunk
    {
        $key = $x . ':' . $z;

        if (!isset($this->chunks[$key])) {
            $this->chunks[$key] = $this->generateChunk($x, $z);
        }

        return $this->chunks[$key];
    }

    private function generateChunk(int $x, int $z): Chunk
    {
        $chunk = new Chunk($x, $z);

        // ===== FLAT WORLD =====
        // y 0   = bedrock
        // y 1-3 = dirt
        // y 4   = grass
        // sisanya udara

        for ($cx = 0; $cx < 16; $cx++) {
            for ($cz = 0; $cz < 16; $cz++) {

                $chunk->setBlock($cx, 0, $cz, Block::BEDROCK);

                for ($y = 1; $y <= 3; $y++) {
                    $chunk->setBlock($cx, $y, $cz, Block::DIRT);
                }

                $chunk->setBlock($cx, 4, $cz, Block::GRASS);
            }
        }

        return $chunk;
    }
}
