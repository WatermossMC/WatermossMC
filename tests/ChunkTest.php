<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use watermossmc\world\Chunk;
use watermossmc\world\SubChunk;
use watermossmc\block\BlockRuntimeData;
use watermossmc\block\BlockRegistry;

class ChunkTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $nbtPath = __DIR__ . '/../resources/canonical_block_states.nbt';
        if (file_exists($nbtPath)) {
            BlockRuntimeData::init($nbtPath);
        }
    }

    public function testSubChunkBlockAccess(): void
    {
        $subChunk = new SubChunk(0);
        $this->assertTrue($subChunk->isEmptyFast());

        $subChunk->setBlock(1, 2, 3, 1);
        $this->assertFalse($subChunk->isEmptyFast());
        $this->assertEquals(1, $subChunk->getBlock(1, 2, 3));
        $this->assertEquals(0, $subChunk->getBlock(0, 0, 0));
    }

    public function testChunkBlockAccessAndBounds(): void
    {
        $chunk = new Chunk(0, 0);
        $chunk->setBlock(5, 64, 5, 1);

        $this->assertEquals(1, $chunk->getBlock(5, 64, 5));
        $this->assertEquals(0, $chunk->getBlock(0, 0, 0));

        $subChunkCount = $chunk->getSubChunkCount();
        $this->assertGreaterThan(0, $subChunkCount);
    }

    public function testBinaryExportImport(): void
    {
        $chunk = new Chunk(1, -1);
        $chunk->setBlock(2, 3, 4, 42);

        $binary = $chunk->exportBinary();
        $restored = Chunk::fromBinary(1, -1, $binary);

        $this->assertEquals(42, $restored->getBlock(2, 3, 4));
    }

    public function testMinMaxSubChunkIndices(): void
    {
        $chunk = new Chunk(0, 0);
        // Test lower bound sub-chunk index (-4) -> y = -4 * 16 = -64
        $chunk->setBlock(0, -64, 0, 10);
        $this->assertEquals(10, $chunk->getBlock(0, -64, 0));

        // Test upper bound sub-chunk index (19) -> y = 19 * 16 + 15 = 319
        $chunk->setBlock(15, 319, 15, 20);
        $this->assertEquals(20, $chunk->getBlock(15, 319, 15));
    }

    public function testEdgeCoordinates(): void
    {
        $subChunk = new SubChunk(0);
        $subChunk->setBlock(0, 0, 0, 5);
        $subChunk->setBlock(15, 15, 15, 6);

        $this->assertEquals(5, $subChunk->getBlock(0, 0, 0));
        $this->assertEquals(6, $subChunk->getBlock(15, 15, 15));
    }

    public function testNetworkEncoding(): void
    {
        $nbtPath = __DIR__ . '/../resources/canonical_block_states.nbt';
        if (!file_exists($nbtPath)) {
            $this->markTestSkipped('canonical_block_states.nbt not found');
        }

        $subChunk = new SubChunk(0);
        $subChunk->setBlock(0, 0, 0, 1);

        $converter = BlockRuntimeData::getConverter();
        $encoded = $subChunk->encode($converter);

        $this->assertNotEmpty($encoded);
        $this->assertIsString($encoded);
    }
}
