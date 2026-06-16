<?php

declare(strict_types=1);

namespace WatermossMC\Minecraft;

use RuntimeException;
use WatermossMC\Minecraft\NBT\NBT;
use WatermossMC\Minecraft\World\Chunk;

final class World
{
    public string $name;

    public int $seed;

    private string $rootPath;

    /** @var array<string, Chunk> */
    private array $chunks = [];

    private int $gameType = 1;

    private int $time = 0;

    private int $dayTime = 0;

    private array $spawn = [
        'x' => 0,
        'y' => 64,
        'z' => 0,
    ];

    public function __construct(string $name, int $seed, string $rootPath)
    {
        $this->name = $name;
        $this->seed = $seed;
        $this->rootPath = rtrim($rootPath, '/\\');

        $this->ensureDirectories();
    }

    public static function load(string $rootPath, string $name, int $seed): self
    {
        $world = new self($name, $seed, $rootPath);

        if (is_file($world->getLevelDatPath())) {
            $world->loadLevelDat();
        } else {
            $world->saveLevelDat();
            file_put_contents($world->getLevelNamePath(), $world->name);
        }

        return $world;
    }

    public function getChunk(int $x, int $z): Chunk
    {
        $key = $x . ':' . $z;

        if (isset($this->chunks[$key])) {
            return $this->chunks[$key];
        }

        $loaded = $this->loadChunkFromDisk($x, $z);
        if ($loaded !== null) {
            return $this->chunks[$key] = $loaded;
        }

        return $this->chunks[$key] = $this->generateChunk($x, $z);
    }

    public function save(): void
    {
        $this->saveLevelDat();
        $this->saveLevelName();
        $this->saveChunks();
    }

    public function saveChunk(Chunk $chunk): void
    {
        $chunkFile = $this->getChunkFilePath($chunk->x, $chunk->z);
        file_put_contents($chunkFile, $chunk->exportBinary());
    }

    public function setSpawnPosition(int $x, int $y, int $z): void
    {
        $this->spawn['x'] = $x;
        $this->spawn['y'] = $y;
        $this->spawn['z'] = $z;
    }

    /**
     * @return array<string, int>
     */
    public function getSpawnPosition(): array
    {
        return $this->spawn;
    }

    private function ensureDirectories(): void
    {
        if (!is_dir($this->rootPath) && !mkdir($this->rootPath, 0777, true) && !is_dir($this->rootPath)) {
            throw new RuntimeException("Failed to create world root: {$this->rootPath}");
        }

        $chunkDirectory = $this->getChunkDirectory();
        if (!is_dir($chunkDirectory) && !mkdir($chunkDirectory, 0777, true) && !is_dir($chunkDirectory)) {
            throw new RuntimeException("Failed to create chunk directory: {$chunkDirectory}");
        }
    }

    private function getChunkDirectory(): string
    {
        return $this->rootPath . '/chunks';
    }

    private function getLevelDatPath(): string
    {
        return $this->rootPath . '/level.dat';
    }

    private function getLevelNamePath(): string
    {
        return $this->rootPath . '/levelname.txt';
    }

    private function getChunkFilePath(int $x, int $z): string
    {
        return $this->getChunkDirectory() . '/chunk_' . $x . '_' . $z . '.dat';
    }

    private function saveChunks(): void
    {
        foreach ($this->chunks as $chunk) {
            $this->saveChunk($chunk);
        }
    }

    private function saveLevelDat(): void
    {
        $payload = NBT::compound([
            'Data' => [
                'LevelName' => $this->name,
                'RandomSeed' => $this->seed,
                'GameType' => $this->gameType,
                'Time' => $this->time,
                'DayTime' => $this->dayTime,
                'LastPlayed' => time(),
                'SpawnX' => $this->spawn['x'],
                'SpawnY' => $this->spawn['y'],
                'SpawnZ' => $this->spawn['z'],
                'generatorName' => 'flat',
                'generatorVersion' => 1,
                'version' => [
                    'Name' => 'WatermossMC',
                    'Id' => 2480,
                ],
            ],
        ]);

        $compressed = gzdeflate($payload, 9);
        if ($compressed === false) {
            throw new RuntimeException('Failed to compress level.dat data');
        }

        file_put_contents($this->getLevelDatPath(), $compressed);
    }

    private function loadLevelDat(): void
    {
        $data = file_get_contents($this->getLevelDatPath());
        if ($data === false) {
            throw new RuntimeException('Failed to read level.dat');
        }

        $decoded = gzinflate($data);
        if ($decoded === false) {
            throw new RuntimeException('Failed to decompress level.dat');
        }

        $nbt = NBT::parse($decoded);
        if (!isset($nbt['Data']) || !is_array($nbt['Data'])) {
            throw new RuntimeException('Invalid level.dat structure');
        }

        $dataCompound = $nbt['Data'];
        $this->name = isset($dataCompound['LevelName']) ? (string)$dataCompound['LevelName'] : $this->name;
        $this->seed = $this->castInt($dataCompound['RandomSeed'] ?? $this->seed);
        $this->gameType = $this->castInt($dataCompound['GameType'] ?? $this->gameType);
        $this->time = $this->castInt($dataCompound['Time'] ?? $this->time);
        $this->dayTime = $this->castInt($dataCompound['DayTime'] ?? $this->dayTime);
        $this->spawn['x'] = $this->castInt($dataCompound['SpawnX'] ?? $this->spawn['x']);
        $this->spawn['y'] = $this->castInt($dataCompound['SpawnY'] ?? $this->spawn['y']);
        $this->spawn['z'] = $this->castInt($dataCompound['SpawnZ'] ?? $this->spawn['z']);
    }

    private function saveLevelName(): void
    {
        file_put_contents($this->getLevelNamePath(), $this->name);
    }

    private function castInt(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    private function loadChunkFromDisk(int $x, int $z): ?Chunk
    {
        $chunkFile = $this->getChunkFilePath($x, $z);
        if (!is_file($chunkFile)) {
            return null;
        }

        $data = file_get_contents($chunkFile);
        if ($data === false) {
            return null;
        }

        return Chunk::fromBinary($x, $z, $data);
    }

    private function generateChunk(int $x, int $z): Chunk
    {
        $chunk = new Chunk($x, $z);

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
