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

namespace watermossmc\block;

use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\Fallable;
use watermossmc\block\utils\FallableTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

use function floor;
use function max;

class SnowLayer extends Flowable implements Fallable
{
	use FallableTrait;

	public const MIN_LAYERS = 1;
	public const MAX_LAYERS = 8;

	protected int $layers = self::MIN_LAYERS;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(self::MIN_LAYERS, self::MAX_LAYERS, $this->layers);
	}

	public function getLayers() : int
	{
		return $this->layers;
	}

	/** @return $this */
	public function setLayers(int $layers) : self
	{
		if ($layers < self::MIN_LAYERS || $layers > self::MAX_LAYERS) {
			throw new \InvalidArgumentException("Layers must be in range " . self::MIN_LAYERS . " ... " . self::MAX_LAYERS);
		}
		$this->layers = $layers;
		return $this;
	}

	public function canBeReplaced() : bool
	{
		return $this->layers < self::MAX_LAYERS;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		//TODO: this zero-height BB is intended to stay in lockstep with a MCPE bug
		return [AxisAlignedBB::one()->trim(Facing::UP, $this->layers >= 4 ? 0.5 : 1)];
	}

	public function getSupportType(int $facing) : SupportType
	{
		if (!$this->canBeReplaced()) {
			return SupportType::FULL;
		}
		return SupportType::NONE;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		return $block->getAdjacentSupportType(Facing::DOWN) === SupportType::FULL;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if ($blockReplace instanceof SnowLayer) {
			if ($blockReplace->layers >= self::MAX_LAYERS) {
				return false;
			}
			$this->layers = $blockReplace->layers + 1;
		}
		if ($this->canBeSupportedAt($blockReplace)) {
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}

		return false;
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	public function onRandomTick() : void
	{
		$world = $this->position->getWorld();
		if ($world->getBlockLightAt($this->position->x, $this->position->y, $this->position->z) >= 12) {
			BlockEventHelper::melt($this, VanillaBlocks::AIR());
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return [
			VanillaItems::SNOWBALL()->setCount(max(1, (int) floor($this->layers / 2)))
		];
	}
}
