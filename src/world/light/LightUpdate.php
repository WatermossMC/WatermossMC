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

namespace watermossmc\world\light;

use watermossmc\math\Facing;

use watermossmc\world\format\SubChunk;
use watermossmc\world\utils\SubChunkExplorer;
use watermossmc\world\utils\SubChunkExplorerStatus;
use watermossmc\world\World;

use function assert;
use function max;

//TODO: make light updates asynchronous
abstract class LightUpdate
{
	public const BASE_LIGHT_FILTER = 1;

	/**
	 * @var int[][] blockhash => [x, y, z, new light level]
	 * @phpstan-var array<int, array{int, int, int, int}>
	 */
	protected array $updateNodes = [];

	/**
	 * @param int[] $lightFilters
	 * @phpstan-param array<int, int> $lightFilters
	 */
	public function __construct(
		protected SubChunkExplorer $subChunkExplorer,
		protected array $lightFilters
	) {
	}

	abstract protected function getCurrent\pocketmine\worldormat\LightArray() : \pocketmine\worldormat\LightArray;

	abstract public function recalculateNode(int $x, int $y, int $z) : void;

	/**
	 * Scans for all light sources in the target chunk and adds them to the propagation queue.
	 * This erases preexisting light in the chunk.
	 */
	abstract public function recalculateChunk(int $chunkX, int $chunkZ) : int;

	protected function getEffectiveLight(int $x, int $y, int $z) : int
	{
		if ($this->subChunkExplorer->moveTo($x, $y, $z) !== SubChunkExplorerStatus::INVALID) {
			return $this->getCurrent\pocketmine\worldormat\LightArray()->get($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK);
		}
		return 0;
	}

	protected function getHighestAdjacentLight(int $x, int $y, int $z) : int
	{
		$adjacent = 0;
		foreach (Facing::OFFSET as [$ox, $oy, $oz]) {
			if (($adjacent = max($adjacent, $this->getEffectiveLight($x + $ox, $y + $oy, $z + $oz))) === 15) {
				break;
			}
		}
		return $adjacent;
	}

	public function setAndUpdateLight(int $x, int $y, int $z, int $newLevel) : void
	{
		$this->updateNodes[World::blockHash($x, $y, $z)] = [$x, $y, $z, $newLevel];
	}

	private function prepareNodes() : LightPropagationContext
	{
		$context = new LightPropagationContext();
		foreach ($this->updateNodes as $blockHash => [$x, $y, $z, $newLevel]) {
			if ($this->subChunkExplorer->moveTo($x, $y, $z) !== SubChunkExplorerStatus::INVALID) {
				$\pocketmine\worldormat\LightArray = $this->getCurrent\pocketmine\worldormat\LightArray();
				$oldLevel = $\pocketmine\worldormat\LightArray->get($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK);

				if ($oldLevel !== $newLevel) {
					$\pocketmine\worldormat\LightArray->set($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK, $newLevel);
					if ($oldLevel < $newLevel) { //light increased
						$context->spreadVisited[$blockHash] = true;
						$context->spreadQueue->enqueue([$x, $y, $z]);
					} else { //light removed
						$context->removalVisited[$blockHash] = true;
						$context->removalQueue->enqueue([$x, $y, $z, $oldLevel]);
					}
				}
			}
		}
		return $context;
	}

	public function execute() : int
	{
		$context = $this->prepareNodes();

		$touched = 0;
		$\pocketmine\worldormat\LightArray = null;
		$subChunkExplorer = $this->subChunkExplorer;
		$subChunkExplorer->invalidate();
		while (!$context->removalQueue->isEmpty()) {
			$touched++;
			[$x, $y, $z, $oldAdjacentLight] = $context->removalQueue->dequeue();

			foreach (Facing::OFFSET as [$ox, $oy, $oz]) {
				$cx = $x + $ox;
				$cy = $y + $oy;
				$cz = $z + $oz;

				$moveStatus = $subChunkExplorer->moveTo($cx, $cy, $cz);
				if ($moveStatus === SubChunkExplorerStatus::INVALID) {
					continue;
				}
				if ($moveStatus === SubChunkExplorerStatus::MOVED) {
					$\pocketmine\worldormat\LightArray = $this->getCurrent\pocketmine\worldormat\LightArray();
				}
				assert($\pocketmine\worldormat\LightArray !== null);
				$this->computeRemoveLight($cx, $cy, $cz, $oldAdjacentLight, $context, $\pocketmine\worldormat\LightArray);
			}
		}

		$subChunk = null;
		$subChunkExplorer->invalidate();
		while (!$context->spreadQueue->isEmpty()) {
			$touched++;
			[$x, $y, $z] = $context->spreadQueue->dequeue();
			$from = $context->spreadVisited[World::blockHash($x, $y, $z)];

			unset($context->spreadVisited[World::blockHash($x, $y, $z)]);

			$moveStatus = $subChunkExplorer->moveTo($x, $y, $z);
			if ($moveStatus === SubChunkExplorerStatus::INVALID) {
				continue;
			}
			if ($moveStatus === SubChunkExplorerStatus::MOVED) {
				$subChunk = $subChunkExplorer->currentSubChunk;
				$\pocketmine\worldormat\LightArray = $this->getCurrent\pocketmine\worldormat\LightArray();
			}
			assert($\pocketmine\worldormat\LightArray !== null);

			$newAdjacentLight = $\pocketmine\worldormat\LightArray->get($x & SubChunk::COORD_MASK, $y & SubChunk::COORD_MASK, $z & SubChunk::COORD_MASK);
			if ($newAdjacentLight <= 0) {
				continue;
			}

			foreach (Facing::OFFSET as $side => [$ox, $oy, $oz]) {
				if ($from === $side) {
					//don't check the side that this node received its initial light from
					continue;
				}
				$cx = $x + $ox;
				$cy = $y + $oy;
				$cz = $z + $oz;

				$moveStatus = $subChunkExplorer->moveTo($cx, $cy, $cz);
				if ($moveStatus === SubChunkExplorerStatus::INVALID) {
					continue;
				}
				if ($moveStatus === SubChunkExplorerStatus::MOVED) {
					$subChunk = $subChunkExplorer->currentSubChunk;
					$\pocketmine\worldormat\LightArray = $this->getCurrent\pocketmine\worldormat\LightArray();
				}
				assert($subChunk !== null);
				$this->computeSpreadLight($cx, $cy, $cz, $newAdjacentLight, $context, $\pocketmine\worldormat\LightArray, $subChunk, $side);
			}
		}

		return $touched;
	}

	protected function computeRemoveLight(int $x, int $y, int $z, int $oldAdjacentLevel, LightPropagationContext $context, \pocketmine\worldormat\LightArray $\pocketmine\worldormat\LightArray) : void
	{
		$lx = $x & SubChunk::COORD_MASK;
		$ly = $y & SubChunk::COORD_MASK;
		$lz = $z & SubChunk::COORD_MASK;
		$current = $\pocketmine\worldormat\LightArray->get($lx, $ly, $lz);

		if ($current !== 0 && $current < $oldAdjacentLevel) {
			$\pocketmine\worldormat\LightArray->set($lx, $ly, $lz, 0);

			if (!isset($context->removalVisited[$index = World::blockHash($x, $y, $z)])) {
				$context->removalVisited[$index] = true;
				if ($current > 1) {
					$context->removalQueue->enqueue([$x, $y, $z, $current]);
				}
			}
		} elseif ($current >= $oldAdjacentLevel) {
			if (!isset($context->spreadVisited[$index = World::blockHash($x, $y, $z)])) {
				$context->spreadVisited[$index] = true;
				$context->spreadQueue->enqueue([$x, $y, $z]);
			}
		}
	}

	protected function computeSpreadLight(int $x, int $y, int $z, int $newAdjacentLevel, LightPropagationContext $context, \pocketmine\worldormat\LightArray $\pocketmine\worldormat\LightArray, SubChunk $subChunk, int $side) : void
	{
		$lx = $x & SubChunk::COORD_MASK;
		$ly = $y & SubChunk::COORD_MASK;
		$lz = $z & SubChunk::COORD_MASK;
		$current = $\pocketmine\worldormat\LightArray->get($lx, $ly, $lz);
		$potentialLight = $newAdjacentLevel - ($this->lightFilters[$subChunk->getBlockStateId($lx, $ly, $lz)] ?? self::BASE_LIGHT_FILTER);

		if ($current < $potentialLight) {
			$\pocketmine\worldormat\LightArray->set($lx, $ly, $lz, $potentialLight);

			if (!isset($context->spreadVisited[$index = World::blockHash($x, $y, $z)]) && $potentialLight > 1) {
				//Track where this node was lit from, to avoid checking the source again when we propagate from here
				//TODO: In the future it might be worth tracking more than one adjacent source face in case multiple
				//nodes try to light the same node. However, this is a rare case since the vast majority of calls are
				//basic propagation with only one source anyway.
				$context->spreadVisited[$index] = Facing::opposite($side);
				$context->spreadQueue->enqueue([$x, $y, $z]);
			}
		}
	}
}
