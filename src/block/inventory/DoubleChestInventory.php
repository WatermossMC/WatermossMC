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

namespace watermossmc\block\inventory;

use watermossmc\inventory\BaseInventory;
use watermossmc\inventory\InventoryHolder;
use watermossmc\item\Item;
use watermossmc\world\sound\ChestCloseSound;
use watermossmc\world\sound\ChestOpenSound;
use watermossmc\world\sound\Sound;

class DoubleChestInventory extends BaseInventory implements BlockInventory, InventoryHolder
{
	use AnimatedBlockInventoryTrait;

	public function __construct(
		private ChestInventory $left,
		private ChestInventory $right
	) {
		$this->holder = $this->left->getHolder();
		parent::__construct();
	}

	public function getInventory() : self
	{
		return $this;
	}

	public function getSize() : int
	{
		return $this->left->getSize() + $this->right->getSize();
	}

	public function getItem(int $index) : Item
	{
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->left->getSize());
	}

	protected function internalSetItem(int $index, Item $item) : void
	{
		$index < $this->left->getSize() ? $this->left->setItem($index, $item) : $this->right->setItem($index - $this->left->getSize(), $item);
	}

	public function getContents(bool $includeEmpty = false) : array
	{
		$result = $this->left->getContents($includeEmpty);
		$leftSize = $this->left->getSize();

		foreach ($this->right->getContents($includeEmpty) as $i => $item) {
			$result[$i + $leftSize] = $item;
		}

		return $result;
	}

	protected function internalSetContents(array $items) : void
	{
		$leftSize = $this->left->getSize();

		$leftContents = [];
		$rightContents = [];

		foreach ($items as $i => $item) {
			if ($i < $this->left->getSize()) {
				$leftContents[$i] = $item;
			} else {
				$rightContents[$i - $leftSize] = $item;
			}
		}
		$this->left->setContents($leftContents);
		$this->right->setContents($rightContents);
	}

	protected function getMatchingItemCount(int $slot, Item $test, bool $checkTags) : int
	{
		$leftSize = $this->left->getSize();
		return $slot < $leftSize ?
			$this->left->getMatchingItemCount($slot, $test, $checkTags) :
			$this->right->getMatchingItemCount($slot - $leftSize, $test, $checkTags);
	}

	public function isSlotEmpty(int $index) : bool
	{
		$leftSize = $this->left->getSize();
		return $index < $leftSize ?
			$this->left->isSlotEmpty($index) :
			$this->right->isSlotEmpty($index - $leftSize);
	}

	protected function getOpenSound() : Sound
	{
		return new ChestOpenSound();
	}

	protected function getCloseSound() : Sound
	{
		return new ChestCloseSound();
	}

	protected function animateBlock(bool $isOpen) : void
	{
		$this->left->animateBlock($isOpen);
		$this->right->animateBlock($isOpen);
	}

	public function getLeftSide() : ChestInventory
	{
		return $this->left;
	}

	public function getRightSide() : ChestInventory
	{
		return $this->right;
	}
}
