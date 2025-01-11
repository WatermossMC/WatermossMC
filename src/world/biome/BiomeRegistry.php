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

namespace watermossmc\world\biome;

use watermossmc\data\bedrock\BiomeIds;
use watermossmc\utils\SingletonTrait;
use watermossmc\world\generator\object\TreeType;

final class BiomeRegistry
{
	use SingletonTrait;

	/**
	 * @var Biome[]|\SplFixedArray
	 * @phpstan-var \SplFixedArray<Biome>
	 */
	private \SplFixedArray $biomes;

	public function __construct()
	{
		$this->biomes = new \SplFixedArray(Biome::MAX_BIOMES);

		$this->register(BiomeIds::OCEAN, new OceanBiome());
		$this->register(BiomeIds::PLAINS, new PlainBiome());
		$this->register(BiomeIds::DESERT, new DesertBiome());
		$this->register(BiomeIds::EXTREME_HILLS, new MountainsBiome());
		$this->register(BiomeIds::FOREST, new ForestBiome());
		$this->register(BiomeIds::TAIGA, new TaigaBiome());
		$this->register(BiomeIds::SWAMPLAND, new SwampBiome());
		$this->register(BiomeIds::RIVER, new RiverBiome());

		$this->register(BiomeIds::HELL, new HellBiome());

		$this->register(BiomeIds::ICE_PLAINS, new IcePlainsBiome());

		$this->register(BiomeIds::EXTREME_HILLS_EDGE, new SmallMountainsBiome());

		$this->register(BiomeIds::BIRCH_FOREST, new ForestBiome(TreeType::BIRCH));
	}

	public function register(int $id, Biome $biome) : void
	{
		$this->biomes[$id] = $biome;
		$biome->setId($id);
	}

	public function getBiome(int $id) : Biome
	{
		if ($this->biomes[$id] === null) {
			$this->register($id, new UnknownBiome());
		}

		return $this->biomes[$id];
	}
}
