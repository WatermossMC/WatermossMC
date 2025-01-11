<?php

/*
 *
 * This file part of WatermossMC.
 *
 *  __        __    _                                    __  __  ____
 *  \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *   \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *    \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *     \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * @author WatermossMC Team
 * @license Apache 2.0
 */

declare(strict_types=1);

namespace watermossmc\world\generator\hell;

use watermossmc\block\VanillaBlocks;
use watermossmc\data\bedrock\BiomeIds;
use watermossmc\world\biome\BiomeRegistry;
use watermossmc\world\ChunkManager;
use watermossmc\world\format\Chunk;
use watermossmc\world\generator\Generator;
use watermossmc\world\generator\InvalidGeneratorOptionsException;
use watermossmc\world\generator\noise\Simplex;
use watermossmc\world\generator\object\OreType;
use watermossmc\world\generator\populator\Ore;
use watermossmc\world\generator\populator\Populator;
use watermossmc\world\World;

use function abs;

class Nether extends Generator
{
	private int $waterHeight = 32;
	private int $emptyHeight = 64;
	private int $emptyAmplitude = 1;
	private float $density = 0.5;

	/** @var Populator[] */
	private array $populators = [];
	/** @var Populator[] */
	private array $generationPopulators = [];
	private Simplex $noiseBase;

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(int $seed, string $preset)
	{
		parent::__construct($seed, $preset);

		$this->noiseBase = new Simplex($this->random, 4, 1 / 4, 1 / 64);
		$this->random->setSeed($this->seed);

		$ores = new Ore();
		$ores->setOreTypes([
			new OreType(VanillaBlocks::NETHER_QUARTZ_ORE(), VanillaBlocks::NETHERRACK(), 16, 14, 10, 117)
		]);
		$this->populators[] = $ores;
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);

		$noise = $this->noiseBase->getFastNoise3D(Chunk::EDGE_LENGTH, 128, Chunk::EDGE_LENGTH, 4, 8, 4, $chunkX * Chunk::EDGE_LENGTH, 0, $chunkZ * Chunk::EDGE_LENGTH);

		//TODO: why don't we just create and set the chunk here directly?
		$chunk = $world->getChunk($chunkX, $chunkZ) ?? throw new \InvalidArgumentException("Chunk $chunkX $chunkZ does not yet exist");

		$bedrock = VanillaBlocks::BEDROCK()->getStateId();
		$netherrack = VanillaBlocks::NETHERRACK()->getStateId();
		$stillLava = VanillaBlocks::LAVA()->getStateId();

		for ($x = 0; $x < Chunk::EDGE_LENGTH; ++$x) {
			for ($z = 0; $z < Chunk::EDGE_LENGTH; ++$z) {
				for ($y = World::Y_MIN; $y < World::Y_MAX; $y++) {
					$chunk->setBiomeId($x, $y, $z, BiomeIds::HELL);
				}

				for ($y = 0; $y < 128; ++$y) {
					if ($y === 0 || $y === 127) {
						$chunk->setBlockStateId($x, $y, $z, $bedrock);
						continue;
					}
					$noiseValue = (abs($this->emptyHeight - $y) / $this->emptyHeight) * $this->emptyAmplitude - $noise[$x][$z][$y];
					$noiseValue -= 1 - $this->density;

					if ($noiseValue > 0) {
						$chunk->setBlockStateId($x, $y, $z, $netherrack);
					} elseif ($y <= $this->waterHeight) {
						$chunk->setBlockStateId($x, $y, $z, $stillLava);
					}
				}
			}
		}

		foreach ($this->generationPopulators as $populator) {
			$populator->populate($world, $chunkX, $chunkZ, $this->random);
		}
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		foreach ($this->populators as $populator) {
			$populator->populate($world, $chunkX, $chunkZ, $this->random);
		}

		$chunk = $world->getChunk($chunkX, $chunkZ);
		$biome = BiomeRegistry::getInstance()->getBiome($chunk->getBiomeId(7, 7, 7));
		$biome->populateChunk($world, $chunkX, $chunkZ, $this->random);
	}
}
