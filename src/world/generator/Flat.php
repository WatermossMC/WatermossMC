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

namespace watermossmc\world\generator;

use watermossmc\block\VanillaBlocks;
use watermossmc\world\ChunkManager;
use watermossmc\world\format\Chunk;
use watermossmc\world\format\SubChunk;
use watermossmc\world\generator\object\OreType;
use watermossmc\world\generator\populator\Ore;
use watermossmc\world\generator\populator\Populator;

use function count;

class Flat extends Generator
{
	private Chunk $chunk;
	/** @var Populator[] */
	private array $populators = [];

	private FlatGeneratorOptions $options;

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function __construct(int $seed, string $preset)
	{
		parent::__construct($seed, $preset !== "" ? $preset : "2;bedrock,2xdirt,grass;1;");
		$this->options = FlatGeneratorOptions::parsePreset($this->preset);

		if (isset($this->options->getExtraOptions()["decoration"])) {
			$ores = new Ore();
			$stone = VanillaBlocks::STONE();
			$ores->setOreTypes([
				new OreType(VanillaBlocks::COAL_ORE(), $stone, 20, 16, 0, 128),
				new OreType(VanillaBlocks::IRON_ORE(), $stone, 20, 8, 0, 64),
				new OreType(VanillaBlocks::REDSTONE_ORE(), $stone, 8, 7, 0, 16),
				new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), $stone, 1, 6, 0, 32),
				new OreType(VanillaBlocks::GOLD_ORE(), $stone, 2, 8, 0, 32),
				new OreType(VanillaBlocks::DIAMOND_ORE(), $stone, 1, 7, 0, 16),
				new OreType(VanillaBlocks::DIRT(), $stone, 20, 32, 0, 128),
				new OreType(VanillaBlocks::GRAVEL(), $stone, 10, 16, 0, 128)
			]);
			$this->populators[] = $ores;
		}

		$this->generateBaseChunk();
	}

	protected function generateBaseChunk() : void
	{
		$this->chunk = new Chunk([], false);

		$structure = $this->options->getStructure();
		$count = count($structure);
		for ($sy = 0; $sy < $count; $sy += SubChunk::EDGE_LENGTH) {
			$subchunk = $this->chunk->getSubChunk($sy >> SubChunk::COORD_BIT_SIZE);
			for ($y = 0; $y < SubChunk::EDGE_LENGTH && isset($structure[$y | $sy]); ++$y) {
				$id = $structure[$y | $sy];

				for ($Z = 0; $Z < SubChunk::EDGE_LENGTH; ++$Z) {
					for ($X = 0; $X < SubChunk::EDGE_LENGTH; ++$X) {
						$subchunk->setBlockStateId($X, $y, $Z, $id);
					}
				}
			}
		}
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void
	{
		$world->setChunk($chunkX, $chunkZ, clone $this->chunk);
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void
	{
		$this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->seed);
		foreach ($this->populators as $populator) {
			$populator->populate($world, $chunkX, $chunkZ, $this->random);
		}

	}
}
