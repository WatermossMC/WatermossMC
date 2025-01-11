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

use watermossmc\world\generator\object\TreeType;
use watermossmc\world\generator\populator\TallGrass;
use watermossmc\world\generator\populator\Tree;

class ForestBiome extends GrassyBiome
{
	private TreeType $type;

	public function __construct(?TreeType $type = null)
	{
		parent::__construct();

		$this->type = $type ?? TreeType::OAK;

		$trees = new Tree($type);
		$trees->setBaseAmount(5);
		$this->addPopulator($trees);

		$tallGrass = new TallGrass();
		$tallGrass->setBaseAmount(3);

		$this->addPopulator($tallGrass);

		$this->setElevation(63, 81);

		if ($this->type === TreeType::BIRCH) {
			$this->temperature = 0.6;
			$this->rainfall = 0.5;
		} else {
			$this->temperature = 0.7;
			$this->rainfall = 0.8;
		}
	}

	public function getName() : string
	{
		return $this->type->getDisplayName() . " Forest";
	}
}
