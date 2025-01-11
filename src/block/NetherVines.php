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

use watermossmc\block\utils\AgeableTrait;
use watermossmc\block\utils\FortuneDropHelper;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\entity\Entity;
use watermossmc\event\block\StructureGrowEvent;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

use function min;
use function mt_rand;

/**
 * This class is used for Weeping & Twisting vines, because they have same behaviour
 */
class NetherVines extends Flowable
{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 25;

	/** Direction the vine grows towards. */
	private int $growthFace;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, int $growthFace)
	{
		$this->growthFace = $growthFace;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function canClimb() : bool
	{
		return true;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$supportBlock = $block->getSide(Facing::opposite($this->growthFace));
		return $supportBlock->getSupportType($this->growthFace)->hasCenterSupport() || $supportBlock->hasSameTypeId($this);
	}

	/**
	 * Returns the block at the end of the vine structure furthest from the supporting block.
	 */
	private function seekToTip() : NetherVines
	{
		$top = $this;
		while (($next = $top->getSide($this->growthFace)) instanceof NetherVines && $next->hasSameTypeId($this)) {
			$top = $next;
		}
		return $top;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->age = mt_rand(0, self::MAX_AGE - 1);
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($item instanceof Fertilizer) {
			if ($this->grow($player, mt_rand(1, 5))) {
				$item->pop();
			}
			return true;
		}
		return false;
	}

	public function ticksRandomly() : bool
	{
		return $this->age < self::MAX_AGE;
	}

	public function onRandomTick() : void
	{
		if ($this->age < self::MAX_AGE && mt_rand(1, 10) === 1) {
			if ($this->getSide($this->growthFace)->canBeReplaced()) {
				$this->grow(null);
			}
		}
	}

	private function grow(?Player $player, int $growthAmount = 1) : bool
	{
		$top = $this->seekToTip();
		$age = $top->age;
		$pos = $top->position;
		$world = $pos->getWorld();
		$changedBlocks = 0;

		$tx = new BlockTransaction($world);

		for ($i = 1; $i <= $growthAmount; $i++) {
			$growthPos = $pos->getSide($this->growthFace, $i);
			if (!$world->isInWorld($growthPos->getFloorX(), $growthPos->getFloorY(), $growthPos->getFloorZ()) || !$world->getBlock($growthPos)->canBeReplaced()) {
				break;
			}
			$tx->addBlock($growthPos, (clone $top)->setAge(min(++$age, self::MAX_AGE)));
			$changedBlocks++;
		}

		if ($changedBlocks > 0) {
			$ev = new StructureGrowEvent($top, $tx, $player);
			$ev->call();

			if ($ev->isCancelled()) {
				return false;
			}

			return $tx->apply();
		}

		return false;
	}

	public function hasEntityCollision() : bool
	{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool
	{
		$entity->resetFallDistance();
		return false;
	}

	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		if (($item->getBlockToolType() & BlockToolType::SHEARS) !== 0 || FortuneDropHelper::bonusChanceFixed($item, 1 / 3, 2 / 9)) {
			return [$this->asItem()];
		}
		return [];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}
}
