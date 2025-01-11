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

namespace watermossmc\world\generator\object;

use watermossmc\block\VanillaBlocks;
use watermossmc\utils\Random;
use watermossmc\world\BlockTransaction;
use watermossmc\world\ChunkManager;

use function abs;

class SpruceTree extends Tree
{
	public function __construct()
	{
		parent::__construct(VanillaBlocks::SPRUCE_LOG(), VanillaBlocks::SPRUCE_LEAVES(), 10);
	}

	protected function generateTrunkHeight(Random $random) : int
	{
		return $this->treeHeight - $random->nextBoundedInt(3);
	}

	public function getBlockTransaction(ChunkManager $world, int $x, int $y, int $z, Random $random) : ?BlockTransaction
	{
		$this->treeHeight = $random->nextBoundedInt(4) + 6;
		return parent::getBlockTransaction($world, $x, $y, $z, $random);
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void
	{
		$topSize = $this->treeHeight - (1 + $random->nextBoundedInt(2));
		$lRadius = 2 + $random->nextBoundedInt(2);
		$radius = $random->nextBoundedInt(2);
		$maxR = 1;
		$minR = 0;

		for ($yy = 0; $yy <= $topSize; ++$yy) {
			$yyy = $y + $this->treeHeight - $yy;

			for ($xx = $x - $radius; $xx <= $x + $radius; ++$xx) {
				$xOff = abs($xx - $x);
				for ($zz = $z - $radius; $zz <= $z + $radius; ++$zz) {
					$zOff = abs($zz - $z);
					if ($xOff === $radius && $zOff === $radius && $radius > 0) {
						continue;
					}

					if (!$transaction->fetchBlockAt($xx, $yyy, $zz)->isSolid()) {
						$transaction->addBlockAt($xx, $yyy, $zz, $this->leafBlock);
					}
				}
			}

			if ($radius >= $maxR) {
				$radius = $minR;
				$minR = 1;
				if (++$maxR > $lRadius) {
					$maxR = $lRadius;
				}
			} else {
				++$radius;
			}
		}
	}
}
