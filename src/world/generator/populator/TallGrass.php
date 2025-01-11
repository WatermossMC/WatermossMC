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

namespace watermossmc\world\generator\populator;

use watermossmc\block\BlockTypeIds;
use watermossmc\block\Leaves;
use watermossmc\block\VanillaBlocks;
use watermossmc\utils\Random;
use watermossmc\world\ChunkManager;
use watermossmc\world\format\Chunk;

class TallGrass implements Populator
{
	private int $randomAmount = 1;
	private int $baseAmount = 0;

	public function setRandomAmount(int $amount) : void
	{
		$this->randomAmount = $amount;
	}

	public function setBaseAmount(int $amount) : void
	{
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $world, int $chunkX, int $chunkZ, Random $random) : void
	{
		$amount = $random->nextRange(0, $this->randomAmount) + $this->baseAmount;

		$block = VanillaBlocks::TALL_GRASS();
		for ($i = 0; $i < $amount; ++$i) {
			$x = $random->nextRange($chunkX * Chunk::EDGE_LENGTH, $chunkX * Chunk::EDGE_LENGTH + (Chunk::EDGE_LENGTH - 1));
			$z = $random->nextRange($chunkZ * Chunk::EDGE_LENGTH, $chunkZ * Chunk::EDGE_LENGTH + (Chunk::EDGE_LENGTH - 1));
			$y = $this->getHighestWorkableBlock($world, $x, $z);

			if ($y !== -1 && $this->canTallGrassStay($world, $x, $y, $z)) {
				$world->setBlockAt($x, $y, $z, $block);
			}
		}
	}

	private function canTallGrassStay(ChunkManager $world, int $x, int $y, int $z) : bool
	{
		$b = $world->getBlockAt($x, $y, $z)->getTypeId();
		return ($b === BlockTypeIds::AIR || $b === BlockTypeIds::SNOW_LAYER) && $world->getBlockAt($x, $y - 1, $z)->getTypeId() === BlockTypeIds::GRASS;
	}

	private function getHighestWorkableBlock(ChunkManager $world, int $x, int $z) : int
	{
		for ($y = 127; $y >= 0; --$y) {
			$b = $world->getBlockAt($x, $y, $z);
			if ($b->getTypeId() !== BlockTypeIds::AIR && !($b instanceof Leaves) && $b->getTypeId() !== BlockTypeIds::SNOW_LAYER) {
				return $y + 1;
			}
		}

		return -1;
	}
}
