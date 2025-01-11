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
use watermossmc\block\utils\BlockEventHelper;
use watermossmc\block\utils\StaticSupportTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\entity\Entity;
use watermossmc\item\Fertilizer;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\Facing;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;
use watermossmc\world\sound\GlowBerriesPickSound;

use function mt_rand;

class CaveVines extends Flowable
{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 25;

	protected bool $berries = false;
	protected bool $head = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(0, self::MAX_AGE, $this->age);
		$w->bool($this->berries);
		$w->bool($this->head);
	}

	public function hasBerries() : bool
	{
		return $this->berries;
	}

	/** @return $this */
	public function setBerries(bool $berries) : self
	{
		$this->berries = $berries;
		return $this;
	}

	public function isHead() : bool
	{
		return $this->head;
	}

	/** @return $this */
	public function setHead(bool $head) : self
	{
		$this->head = $head;
		return $this;
	}

	public function canClimb() : bool
	{
		return true;
	}

	public function getLightLevel() : int
	{
		return $this->berries ? 14 : 0;
	}

	private function canBeSupportedAt(Block $block) : bool
	{
		$supportBlock = $block->getSide(Facing::UP);
		return $supportBlock->getSupportType(Facing::DOWN) === SupportType::FULL || $supportBlock->hasSameTypeId($this);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		$this->age = mt_rand(0, self::MAX_AGE);
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool
	{
		if ($this->berries) {
			$this->position->getWorld()->dropItem($this->position, $this->asItem());
			$this->position->getWorld()->addSound($this->position, new GlowBerriesPickSound());

			$this->position->getWorld()->setBlock($this->position, $this->setBerries(false));
			return true;
		}
		if ($item instanceof Fertilizer) {
			$newState = (clone $this)
				->setBerries(true)
				->setHead(!$this->getSide(Facing::DOWN)->hasSameTypeId($this));
			if (BlockEventHelper::grow($this, $newState, $player)) {
				$item->pop();
			}
			return true;
		}
		return false;
	}

	public function onRandomTick() : void
	{
		$head = !$this->getSide(Facing::DOWN)->hasSameTypeId($this);
		if ($head !== $this->head) {
			$this->position->getWorld()->setBlock($this->position, $this->setHead($head));
		}

		if ($this->age < self::MAX_AGE && mt_rand(1, 10) === 1) {
			$growthPos = $this->position->getSide(Facing::DOWN);
			$world = $growthPos->getWorld();
			if ($world->isInWorld($growthPos->getFloorX(), $growthPos->getFloorY(), $growthPos->getFloorZ())) {
				$block = $world->getBlock($growthPos);
				if ($block->getTypeId() === BlockTypeIds::AIR) {
					$newState = VanillaBlocks::CAVE_VINES()
						->setAge($this->age + 1)
						->setBerries(mt_rand(1, 9) === 1);
					BlockEventHelper::grow($block, $newState, null);
				}
			}
		}
	}

	public function ticksRandomly() : bool
	{
		return true;
	}

	protected function recalculateCollisionBoxes() : array
	{
		return [];
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

	public function getDropsForCompatibleTool(Item $item) : array
	{
		return $this->berries ? [$this->asItem()] : [];
	}

	public function isAffectedBySilkTouch() : bool
	{
		return true;
	}

	public function asItem() : Item
	{
		return VanillaItems::GLOW_BERRIES();
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}
}
