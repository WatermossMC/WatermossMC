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

use PHPUnit\Framework\TestCase;
use watermossmc\block\utils\BrewingStandSlot;

use function count;

class BrewingStandTest extends TestCase
{
	/**
	 * @phpstan-return \Generator<int, array{list<BrewingStandSlot>}, void, void>
	 */
	public static function slotsProvider() : \Generator
	{
		yield [BrewingStandSlot::cases()];
		yield [[BrewingStandSlot::EAST]];
		yield [[BrewingStandSlot::EAST, BrewingStandSlot::NORTHWEST]];
	}

	/**
	 * @dataProvider slotsProvider
	 *
	 * @param BrewingStandSlot[] $slots
	 * @phpstan-param list<BrewingStandSlot> $slots
	 */
	public function testHasAndSetSlot(array $slots) : void
	{
		$block = VanillaBlocks::BREWING_STAND();
		foreach ($slots as $slot) {
			$block->setSlot($slot, true);
		}
		foreach ($slots as $slot) {
			self::assertTrue($block->hasSlot($slot));
		}

		foreach ($slots as $slot) {
			$block->setSlot($slot, false);
		}
		foreach ($slots as $slot) {
			self::assertFalse($block->hasSlot($slot));
		}
	}

	/**
	 * @dataProvider slotsProvider
	 *
	 * @param BrewingStandSlot[] $slots
	 * @phpstan-param list<BrewingStandSlot> $slots
	 */
	public function testGetSlots(array $slots) : void
	{
		$block = VanillaBlocks::BREWING_STAND();

		foreach ($slots as $slot) {
			$block->setSlot($slot, true);
		}

		self::assertCount(count($slots), $block->getSlots());

		foreach ($slots as $slot) {
			$block->setSlot($slot, false);
		}
		self::assertCount(0, $block->getSlots());
	}

	/**
	 * @dataProvider slotsProvider
	 *
	 * @param BrewingStandSlot[] $slots
	 * @phpstan-param list<BrewingStandSlot> $slots
	 */
	public function testSetSlots(array $slots) : void
	{
		$block = VanillaBlocks::BREWING_STAND();

		$block->setSlots($slots);
		foreach ($slots as $slot) {
			self::assertTrue($block->hasSlot($slot));
		}
		$block->setSlots([]);
		self::assertCount(0, $block->getSlots());
		foreach ($slots as $slot) {
			self::assertFalse($block->hasSlot($slot));
		}
	}
}
