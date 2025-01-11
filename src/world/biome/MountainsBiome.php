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

use watermossmc\block\VanillaBlocks;
use watermossmc\world\generator\object\OreType;
use watermossmc\world\generator\populator\Ore;
use watermossmc\world\generator\populator\TallGrass;
use watermossmc\world\generator\populator\Tree;

class MountainsBiome extends GrassyBiome
{
	public function __construct()
	{
		parent::__construct();

		$trees = new Tree();
		$trees->setBaseAmount(1);
		$this->addPopulator($trees);

		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(1);

		$this->addPopulator($tallGrass);

		$ores = new Ore();
		$ores->setOreTypes([
			new OreType(VanillaBlocks::EMERALD_ORE(), VanillaBlocks::STONE(), 11, 1, 0, 32)
		]);

		$this->addPopulator($ores);

		$this->setElevation(63, 127);

		$this->temperature = 0.4;
		$this->rainfall = 0.5;
	}

	public function getName() : string
	{
		return "Mountains";
	}
}
