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

namespace watermossmc\inventory;

use PHPUnit\Framework\TestCase;
use watermossmc\item\Item;
use watermossmc\item\VanillaItems;

class BaseInventoryTest extends TestCase
{
	public function testAddItemDifferentUserData() : void
	{
		$inv = new SimpleInventory(1);
		$item1 = VanillaItems::ARROW()->setCount(1);
		$item2 = VanillaItems::ARROW()->setCount(1)->setCustomName("TEST");

		$inv->addItem(clone $item1);
		self::assertFalse($inv->canAddItem($item2), "Item WITHOUT userdata should not stack with item WITH userdata");
		self::assertNotEmpty($inv->addItem($item2));

		$inv->clearAll();
		self::assertEmpty($inv->getContents());

		$inv->addItem(clone $item2);
		self::assertFalse($inv->canAddItem($item1), "Item WITH userdata should not stack with item WITHOUT userdata");
		self::assertNotEmpty($inv->addItem($item1));
	}

	/**
	 * @return Item[]
	 */
	private function getTestItems() : array
	{
		return [
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16),
			VanillaItems::APPLE()->setCount(16)
		];
	}

	public function testAddMultipleItemsInOneCall() : void
	{
		$inventory = new SimpleInventory(1);
		$leftover = $inventory->addItem(...$this->getTestItems());
		self::assertCount(0, $leftover);
		self::assertTrue($inventory->getItem(0)->equalsExact(VanillaItems::APPLE()->setCount(64)));
	}

	public function testAddMultipleItemsInOneCallWithLeftover() : void
	{
		$inventory = new SimpleInventory(1);
		$inventory->setItem(0, VanillaItems::APPLE()->setCount(20));
		$leftover = $inventory->addItem(...$this->getTestItems());
		self::assertCount(2, $leftover); //the leftovers are not currently stacked - if they were given separately, they'll be returned separately
		self::assertTrue($inventory->getItem(0)->equalsExact(VanillaItems::APPLE()->setCount(64)));

		$leftoverCount = 0;
		foreach ($leftover as $item) {
			self::assertTrue($item->equals(VanillaItems::APPLE()));
			$leftoverCount += $item->getCount();
		}
		self::assertSame(20, $leftoverCount);
	}

	public function testAddItemWithOversizedCount() : void
	{
		$inventory = new SimpleInventory(10);
		$leftover = $inventory->addItem(VanillaItems::APPLE()->setCount(100));
		self::assertCount(0, $leftover);

		$count = 0;
		foreach ($inventory->getContents() as $item) {
			self::assertTrue($item->equals(VanillaItems::APPLE()));
			$count += $item->getCount();
		}
		self::assertSame(100, $count);
	}

	public function testGetAddableItemQuantityStacking() : void
	{
		$inventory = new SimpleInventory(1);
		$inventory->addItem(VanillaItems::APPLE()->setCount(60));
		self::assertSame(2, $inventory->getAddableItemQuantity(VanillaItems::APPLE()->setCount(2)));
		self::assertSame(4, $inventory->getAddableItemQuantity(VanillaItems::APPLE()->setCount(6)));
	}

	public function testGetAddableItemQuantityEmptyStack() : void
	{
		$inventory = new SimpleInventory(1);
		$item = VanillaItems::APPLE();
		$item->setCount($item->getMaxStackSize());
		self::assertSame($item->getMaxStackSize(), $inventory->getAddableItemQuantity($item));
	}
}
