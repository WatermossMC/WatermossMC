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
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\utils\Random;
use watermossmc\world\BlockTransaction;

use function abs;
use function array_rand;

final class AcaciaTree extends Tree
{
	private const MIN_HEIGHT = 5;

	private ?Vector3 $mainBranchTip = null;
	private ?Vector3 $secondBranchTip = null;

	public function __construct()
	{
		parent::__construct(
			VanillaBlocks::ACACIA_LOG(),
			VanillaBlocks::ACACIA_LEAVES(),
			0 //we don't use this anyway - everything is overridden
		);
	}

	protected function generateTrunkHeight(Random $random) : int
	{
		//50% chance of 2 extra blocks, 33% chance 1 or 3, 17% chance 0 or 4
		return self::MIN_HEIGHT + $random->nextRange(0, 2) + $random->nextRange(0, 2);
	}

	protected function placeTrunk(int $x, int $y, int $z, Random $random, int $trunkHeight, BlockTransaction $transaction) : void
	{
		// The base dirt block
		$transaction->addBlockAt($x, $y - 1, $z, VanillaBlocks::DIRT());

		$firstBranchHeight = $trunkHeight - 1 - $random->nextRange(0, 3);

		for ($yy = 0; $yy <= $firstBranchHeight; ++$yy) {
			$transaction->addBlockAt($x, $y + $yy, $z, $this->trunkBlock);
		}

		$mainBranchFacing = Facing::HORIZONTAL[array_rand(Facing::HORIZONTAL)];

		//this branch may grow a second trunk if the diagonal length is less than the max length
		$this->mainBranchTip = $this->placeBranch(
			$transaction,
			new Vector3($x, $y + $firstBranchHeight, $z),
			$mainBranchFacing,
			$random->nextRange(1, 3),
			$trunkHeight - $firstBranchHeight
		);

		$secondBranchFacing = Facing::HORIZONTAL[array_rand(Facing::HORIZONTAL)];
		if ($secondBranchFacing !== $mainBranchFacing) {
			$secondBranchLength = $random->nextRange(1, 3);
			$this->secondBranchTip = $this->placeBranch(
				$transaction,
				new Vector3($x, $y + ($firstBranchHeight - $random->nextRange(0, 1)), $z),
				$secondBranchFacing,
				$secondBranchLength,
				$secondBranchLength //the secondary branch may not form a second trunk
			);
		}
	}

	protected function placeBranch(BlockTransaction $transaction, Vector3 $start, int $branchFacing, int $maxDiagonal, int $length) : Vector3
	{
		$diagonalPlaced = 0;

		$nextBlockPos = $start;
		for ($yy = 0; $yy < $length; $yy++) {
			$nextBlockPos = $nextBlockPos->up();
			if ($diagonalPlaced < $maxDiagonal) {
				$nextBlockPos = $nextBlockPos->getSide($branchFacing);
				$diagonalPlaced++;
			}
			$transaction->addBlock($nextBlockPos, $this->trunkBlock);
		}

		return $nextBlockPos;
	}

	protected function placeCanopyLayer(BlockTransaction $transaction, Vector3 $center, int $radius, int $maxTaxicabDistance) : void
	{
		$centerX = $center->getFloorX();
		$centerY = $center->getFloorY();
		$centerZ = $center->getFloorZ();

		for ($x = $centerX - $radius; $x <= $centerX + $radius; ++$x) {
			for ($z = $centerZ - $radius; $z <= $centerZ + $radius; ++$z) {
				if (
					abs($x - $centerX) + abs($z - $centerZ) <= $maxTaxicabDistance &&
					$transaction->fetchBlockAt($x, $centerY, $z)->canBeReplaced()
				) {
					$transaction->addBlockAt($x, $centerY, $z, $this->leafBlock);
				}
			}
		}
	}

	protected function placeCanopy(int $x, int $y, int $z, Random $random, BlockTransaction $transaction) : void
	{
		$mainBranchTip = $this->mainBranchTip;
		if ($mainBranchTip !== null) {
			$this->placeCanopyLayer($transaction, $mainBranchTip, radius: 3, maxTaxicabDistance: 5);
			$this->placeCanopyLayer($transaction, $mainBranchTip->up(), radius: 2, maxTaxicabDistance: 2);
		}
		$secondBranchTip = $this->secondBranchTip;
		if ($secondBranchTip !== null) {
			$this->placeCanopyLayer($transaction, $secondBranchTip, radius: 2, maxTaxicabDistance: 3);
			$this->placeCanopyLayer($transaction, $secondBranchTip->up(), radius: 1, maxTaxicabDistance: 2);
		}
	}
}
