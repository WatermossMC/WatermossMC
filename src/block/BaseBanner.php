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

use watermossmc\block\tile\Banner as TileBanner;
use watermossmc\block\utils\BannerPatternLayer;
use watermossmc\block\utils\ColoredTrait;
use watermossmc\block\utils\SupportType;
use watermossmc\item\Banner as ItemBanner;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Vector3;
use watermossmc\player\Player;
use watermossmc\world\BlockTransaction;

use function assert;
use function count;

abstract class BaseBanner extends Transparent
{
	use ColoredTrait;

	/**
	 * @var BannerPatternLayer[]
	 * @phpstan-var list<BannerPatternLayer>
	 */
	protected array $patterns = [];

	public function readStateFromWorld() : Block
	{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if ($tile instanceof TileBanner) {
			$this->color = $tile->getBaseColor();
			$this->setPatterns($tile->getPatterns());
		}

		return $this;
	}

	public function writeStateToWorld() : void
	{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof TileBanner);
		$tile->setBaseColor($this->color);
		$tile->setPatterns($this->patterns);
	}

	public function isSolid() : bool
	{
		return false;
	}

	public function getMaxStackSize() : int
	{
		return 16;
	}

	/**
	 * @return BannerPatternLayer[]
	 * @phpstan-return list<BannerPatternLayer>
	 */
	public function getPatterns() : array
	{
		return $this->patterns;
	}

	/**
	 * @param BannerPatternLayer[] $patterns
	 *
	 * @phpstan-param list<BannerPatternLayer> $patterns
	 * @return $this
	 */
	public function setPatterns(array $patterns) : self
	{
		foreach ($patterns as $pattern) {
			if (!$pattern instanceof BannerPatternLayer) {
				throw new \TypeError("Array must only contain " . BannerPatternLayer::class . " objects");
			}
		}
		$this->patterns = $patterns;
		return $this;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [];
	}

	public function getSupportType(int $facing) : SupportType
	{
		return SupportType::NONE;
	}

	private function canBeSupportedBy(Block $block) : bool
	{
		return $block->isSolid();
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool
	{
		if (!$this->canBeSupportedBy($blockReplace->getSide($this->getSupportingFace()))) {
			return false;
		}
		if ($item instanceof ItemBanner) {
			$this->color = $item->getColor();
			$this->setPatterns($item->getPatterns());
		}

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	abstract protected function getSupportingFace() : int;

	public function onNearbyBlockChange() : void
	{
		if (!$this->canBeSupportedBy($this->getSide($this->getSupportingFace()))) {
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function getDropsForCompatibleTool(Item $item) : array
	{
		$drop = $this->asItem();
		if ($drop instanceof ItemBanner && count($this->patterns) > 0) {
			$drop->setPatterns($this->patterns);
		}

		return [$drop];
	}

	public function getPickedItem(bool $addUserData = false) : Item
	{
		$result = $this->asItem();
		if ($addUserData && $result instanceof ItemBanner && count($this->patterns) > 0) {
			$result->setPatterns($this->patterns);
		}
		return $result;
	}

	public function asItem() : Item
	{
		return VanillaItems::BANNER()->setColor($this->color);
	}
}
