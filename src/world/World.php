<?php

<<<<<<< HEAD
=======
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

>>>>>>> 866a1c0 (...)
declare(strict_types=1);

namespace watermossmc\world;

use RuntimeException;
<<<<<<< HEAD
use watermossmc\nbt\NBT;
=======
use watermossmc\block\Block;
use watermossmc\entity\EntityManager;
use watermossmc\nbt\NBT;
use watermossmc\util\Config;
use watermossmc\util\Location;
>>>>>>> 866a1c0 (...)

final class World
{
    private const CHUNK_SIZE = 16;
    private const MIN_TERRAIN_Y = 56;
    private const MAX_TERRAIN_Y = 76;
    private const SPAWN_SEARCH_RADIUS = 32;
    private const SPAWN_PLATFORM_RADIUS = 2;

    public string $name;

    public int $seed;

    private string $rootPath;

    /** @var array<string, Chunk> */
    private array $chunks = [];

    private int $gameType = 1;

    private int $time = 0;

    private int $dayTime = 0;

<<<<<<< HEAD
=======
    private EntityManager $entities;

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): void
    {
        $this->time = $time;
        $this->dayTime = $time % 24000;
    }

    public function getDayTime(): int
    {
        return $this->dayTime;
    }

    public function setDayTime(int $dayTime): void
    {
        $this->dayTime = $dayTime;
        $days = (int) floor($this->time / 24000);
        $this->time = ($days * 24000) + $dayTime;
    }

    public function tickTime(): void
    {
        $this->time++;
        $this->dayTime = $this->time % 24000;
    }

    public function getGameType(): int
    {
        return $this->gameType;
    }

>>>>>>> 866a1c0 (...)
    /** @var array{x: int, y: int, z: int} */
    private array $spawn = [
        'x' => 0,
        'y' => 65,
        'z' => 0,
    ];

    public function __construct(string $name, int $seed, string $rootPath)
    {
        $this->name = $name;
        $this->seed = $seed;
        $this->rootPath = rtrim($rootPath, '/\\');

<<<<<<< HEAD
        $this->ensureDirectories();
    }

=======
        $this->entities = new EntityManager($this);

        $this->ensureDirectories();
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entities;
    }

>>>>>>> 866a1c0 (...)
    public static function load(string $rootPath, string $name, int $seed): self
    {
        $world = new self($name, $seed, $rootPath);

        if (is_file($world->getLevelDatPath())) {
            $world->loadLevelDat();
        } else {
            $world->setSafeSpawnPosition();
            $world->saveLevelDat();
            $world->saveLevelName();
        }

        $world->setSafeSpawnPosition();
        $world->generateSpawnArea(2);

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

    public function isChunkLoaded(int $x, int $z): bool
    {
        return isset($this->chunks[$x . ':' . $z]);
    }

    /**
     * @return array<string, Chunk>
     */
    public function getLoadedChunks(): array
    {
        return $this->chunks;
    }

    public function unloadChunk(int $x, int $z, bool $save = true): void
    {
        $key = $x . ':' . $z;
        if (!isset($this->chunks[$key])) {
            return;
        }

        if ($save) {
            $this->saveChunk($this->chunks[$key]);
        }

        unset($this->chunks[$key]);
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
     * @return array{x:int, y:int, z:int}
     */
    public function getSpawnPosition(): array
    {
        return $this->spawn;
    }

    /**
     * @return array{x:float, y:float, z:float}
     */
    public function getPlayerSpawnPosition(): array
    {
        return [
            'x' => $this->spawn['x'] + 0.5,
            'y' => (float) $this->spawn['y'],
            'z' => $this->spawn['z'] + 0.5,
        ];
    }

    public function generateSpawnArea(int $radiusChunks = 2): void
    {
        $spawnChunkX = $this->blockToChunk($this->spawn['x']);
        $spawnChunkZ = $this->blockToChunk($this->spawn['z']);

        for ($x = $spawnChunkX - $radiusChunks; $x <= $spawnChunkX + $radiusChunks; $x++) {
            for ($z = $spawnChunkZ - $radiusChunks; $z <= $spawnChunkZ + $radiusChunks; $z++) {
                $this->getChunk($x, $z);
            }
        }
    }

    public function getSurfaceY(int $x, int $z): int
    {
        $chunk = $this->getChunk($this->blockToChunk($x), $this->blockToChunk($z));
        $localX = $this->blockToLocal($x);
        $localZ = $this->blockToLocal($z);

        for ($y = 127; $y >= 0; $y--) {
<<<<<<< HEAD
            if ($chunk->getBlock($localX, $y, $localZ) !== \watermossmc\block\Block::AIR) {
=======
            if ($chunk->getBlock($localX, $y, $localZ) !== Block::AIR) {
>>>>>>> 866a1c0 (...)
                return $y;
            }
        }

        return self::MIN_TERRAIN_Y;
    }

    public function getBlockAt(int $x, int $y, int $z): int
    {
        $chunk = $this->getChunk($this->blockToChunk($x), $this->blockToChunk($z));

        return $chunk->getBlock($this->blockToLocal($x), $y, $this->blockToLocal($z));
    }

<<<<<<< HEAD
    public function getBlockAtLocation(\watermossmc\util\Location $location): int
=======
    public function getBlockAtLocation(Location $location): int
>>>>>>> 866a1c0 (...)
    {
        [$x, $y, $z] = $location->blockToInteger();
        return $this->getBlockAt($x, $y, $z);
    }

    public function setBlockAt(int $x, int $y, int $z, int $blockId): void
    {
        $chunk = $this->getChunk($this->blockToChunk($x), $this->blockToChunk($z));
        $chunk->setBlock($this->blockToLocal($x), $y, $this->blockToLocal($z), $blockId);
    }

<<<<<<< HEAD
    public function setBlockAtLocation(\watermossmc\util\Location $location, int $blockId): void
=======
    public function setBlockAtLocation(Location $location, int $blockId): void
>>>>>>> 866a1c0 (...)
    {
        [$x, $y, $z] = $location->blockToInteger();
        $this->setBlockAt($x, $y, $z, $blockId);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSeed(): int
    {
        return $this->seed;
    }

<<<<<<< HEAD
=======
    public function getChunkViewDistance(): int
    {
        return Config::getInt("chunk-view-radius", 19);
    }

>>>>>>> 866a1c0 (...)
    private function setSafeSpawnPosition(): void
    {
        $best = ['x' => 0, 'z' => 0, 'score' => \PHP_INT_MAX];

        for ($x = -self::SPAWN_SEARCH_RADIUS; $x <= self::SPAWN_SEARCH_RADIUS; $x++) {
            for ($z = -self::SPAWN_SEARCH_RADIUS; $z <= self::SPAWN_SEARCH_RADIUS; $z++) {
                $score = (abs($x) + abs($z)) * 4;
                $centerHeight = $this->terrainHeightAt($x, $z);
                $maxDelta = 0;

                for ($dx = -self::SPAWN_PLATFORM_RADIUS; $dx <= self::SPAWN_PLATFORM_RADIUS; $dx++) {
                    for ($dz = -self::SPAWN_PLATFORM_RADIUS; $dz <= self::SPAWN_PLATFORM_RADIUS; $dz++) {
                        $maxDelta = max($maxDelta, abs($centerHeight - $this->terrainHeightAt($x + $dx, $z + $dz)));
                    }
                }

                $score += $maxDelta * 25;
                if ($score < $best['score']) {
                    $best = ['x' => $x, 'z' => $z, 'score' => $score];
                }
            }
        }

        $surfaceY = $this->terrainHeightAt($best['x'], $best['z']);
        $this->setSpawnPosition($best['x'], $surfaceY + 1, $best['z']);
    }

    private function ensureDirectories(): void
    {
        if (!is_dir($this->rootPath) && !mkdir($this->rootPath, 0o777, true) && !is_dir($this->rootPath)) {
            throw new RuntimeException("Failed to create world root: {$this->rootPath}");
        }

        $chunkDirectory = $this->getChunkDirectory();
        if (!is_dir($chunkDirectory) && !mkdir($chunkDirectory, 0o777, true) && !is_dir($chunkDirectory)) {
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
                'generatorName' => 'watermoss_overworld',
                'generatorVersion' => 2,
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
        if (!isset($nbt['Data']) || !\is_array($nbt['Data'])) {
            throw new RuntimeException('Invalid level.dat structure');
        }

        $dataCompound = $nbt['Data'];
        $this->name = isset($dataCompound['LevelName']) ? (string) $dataCompound['LevelName'] : $this->name;
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
        if (\is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
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

        for ($cx = 0; $cx < self::CHUNK_SIZE; $cx++) {
            for ($cz = 0; $cz < self::CHUNK_SIZE; $cz++) {
                $worldX = ($x * self::CHUNK_SIZE) + $cx;
                $worldZ = ($z * self::CHUNK_SIZE) + $cz;
                $surfaceY = $this->terrainHeightAt($worldX, $worldZ);

                if ($this->isInsideSpawnPlatform($worldX, $worldZ)) {
                    $surfaceY = $this->spawn['y'] - 1;
                }

<<<<<<< HEAD
                $chunk->setBlock($cx, 0, $cz, \watermossmc\block\Block::BEDROCK);

                for ($y = 1; $y <= $surfaceY; $y++) {
                    $blockId = match (true) {
                        $y === $surfaceY => \watermossmc\block\Block::GRASS,
                        $y >= $surfaceY - 3 => \watermossmc\block\Block::DIRT,
                        default => \watermossmc\block\Block::STONE,
=======
                $chunk->setBlock($cx, 0, $cz, Block::BEDROCK);

                for ($y = 1; $y <= $surfaceY; $y++) {
                    $blockId = match (true) {
                        $y === $surfaceY => Block::GRASS,
                        $y >= $surfaceY - 3 => Block::DIRT,
                        default => Block::STONE,
>>>>>>> 866a1c0 (...)
                    };

                    $chunk->setBlock($cx, $y, $cz, $blockId);
                }
            }
        }

        return $chunk;
    }

    private function terrainHeightAt(int $x, int $z): int
    {
        $lowFrequency = $this->valueNoise($x, $z, 48) * 10.0;
        $midFrequency = $this->valueNoise($x + 991, $z - 313, 18) * 4.0;
        $height = 64 + (int) round($lowFrequency + $midFrequency);

        return max(self::MIN_TERRAIN_Y, min(self::MAX_TERRAIN_Y, $height));
    }

    private function valueNoise(int $x, int $z, int $scale): float
    {
        $cellX = $this->floorDiv($x, $scale);
        $cellZ = $this->floorDiv($z, $scale);
        $localX = ($x - ($cellX * $scale)) / $scale;
        $localZ = ($z - ($cellZ * $scale)) / $scale;

        $northWest = $this->randomUnit($cellX, $cellZ);
        $northEast = $this->randomUnit($cellX + 1, $cellZ);
        $southWest = $this->randomUnit($cellX, $cellZ + 1);
        $southEast = $this->randomUnit($cellX + 1, $cellZ + 1);

        $fadeX = $this->smoothStep($localX);
        $fadeZ = $this->smoothStep($localZ);
        $north = $this->lerp($northWest, $northEast, $fadeX);
        $south = $this->lerp($southWest, $southEast, $fadeX);

        return $this->lerp($north, $south, $fadeZ);
    }

    private function randomUnit(int $x, int $z): float
    {
        $hash = ($x * 73428767) ^ ($z * 912931) ^ $this->seed;
        $hash = ($hash ^ ($hash >> 13)) * 1274126177;
        $hash ^= $hash >> 16;

        return (($hash & 0xffff) / 32767.5) - 1.0;
    }

    private function smoothStep(float $value): float
    {
        return $value * $value * (3.0 - (2.0 * $value));
    }

    private function lerp(float $a, float $b, float $t): float
    {
        return $a + (($b - $a) * $t);
    }

    private function isInsideSpawnPlatform(int $x, int $z): bool
    {
        return abs($x - $this->spawn['x']) <= self::SPAWN_PLATFORM_RADIUS
            && abs($z - $this->spawn['z']) <= self::SPAWN_PLATFORM_RADIUS;
    }

    private function blockToChunk(int $coordinate): int
    {
        return $this->floorDiv($coordinate, self::CHUNK_SIZE);
    }

    private function blockToLocal(int $coordinate): int
    {
        $local = $coordinate % self::CHUNK_SIZE;

        return $local < 0 ? $local + self::CHUNK_SIZE : $local;
    }

    private function floorDiv(int $value, int $divisor): int
    {
        $result = intdiv($value, $divisor);

        if ($value < 0 && $value % $divisor !== 0) {
            $result--;
        }

        return $result;
    }
}
