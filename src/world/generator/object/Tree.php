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

use watermossmc\block\Block;
use watermossmc\block\Leaves;
use watermossmc\block\Sapling;
use watermossmc\block\VanillaBlocks;
use watermossmc\utils\Random;
use watermossmc\world\BlockTransaction;
use watermossmc\world\ChunkManager;

use function abs;

abstract class Tree
{
	public function __construct(
		protected Block $trunkBlock,
		protected Block $leafBlock,
		protected int $treeHeight = 7
	) {
	}

	public function canPlaceObject(ChunkManager $world, int $x, int $y, int $z, Random $random) : bool
	{
		$radiusToCheck = 0;
		for ($yy = 0; $yy < $this->treeHeight + 3; ++$yy) {
			if ($yy === 1 || $yy === $this->treeHeight) {
				++$radiusToCheck;
			}
			for ($xx = -$radiusToCheck; $xx < ($radiusToCheck + 1); ++$xx) {
				for ($zz = -$radiusToCheck; $zz < ($radiusToCheck + 1); ++$zz) {
					if (!$this->canOverride($world->getBlockAt($x + $xx, $y + $yy, $z + $zz))) {
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Returns the BlockTransaction containing all the blocks the tree would change upon growing at the given coordinates
	 * or null if the tree can't be grown
	 */
	public function getBlockTransaction(ChunkManager $world, int $x, int $y, int $z, Random $random) : ?BlockTransaction
	{
		if (!$this->canPlaceObject($world, $x, $y, $z, $random)) {
			return null;
		}

		$transaction = new BlockTransaction($world);
		$this->placeTrunk($x, $y, $z, $random, $this->generateTrunkHeight($random), $transaction);
		$this->placeCanopy($x, $y, $z, $random, $transaction);

		return $transaction;
	}

	protected function generateTrunkHeight(Random $random) : int
	{
		return $this->treeHeight - 1;
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $trunkHeight, BlockTransaction $transaction) : void
	{
		// The base dirt block
		$transaction->addBlockAt($x, $y - 1, $z, VanillaBlocks::DIRT());

		for ($yy = 0; $yy < $trunkHeight; ++$yy) {
			if ($this->canOverride($transaction->fetchBlockAt($x, $y + $yy, $z))) {
				$transaction->addBlockAt($x, $y + $yy, $z, $this->trunkBlock);
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void
	{
		for ($yy = $y - 3 + $this->treeHeight; $yy <= $y + $this->treeHeight; ++$yy) {
			$yOff = $yy - ($y + $this->treeHeight);
			$mid = (int) (1 - $yOff / 2);
			for ($xx = $x - $mid; $xx <= $x + $mid; ++$xx) {
				$xOff = abs($xx - $x);
				for ($zz = $z - $mid; $zz <= $z + $mid; ++$zz) {
					$zOff = abs($zz - $z);
					if ($xOff === $mid && $zOff === $mid && ($yOff === 0 || $random->nextBoundedInt(2) === 0)) {
						continue;
					}
					if (!$transaction->fetchBlockAt($xx, $yy, $zz)->isSolid()) {
						$transaction->addBlockAt($xx, $yy, $zz, $this->leafBlock);
					}
				}
			}
		}
	}

	protected function canOverride(Block $block) : bool
	{
		return $block->canBeReplaced() || $block instanceof Sapling || $block instanceof Leaves;
	}
}
