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

namespace watermossmc\item;

use PHPUnit\Framework\TestCase;
use watermossmc\utils\Utils;

use function array_unique;
use function max;

class ItemTypeIdsTest extends TestCase
{
	public function testFirstUnused() : void
	{
		$reflect = new \ReflectionClass(ItemTypeIds::class);

		$constants = $reflect->getConstants();
		unset($constants['FIRST_UNUSED_ITEM_ID']);

		self::assertSame($reflect->getConstant('FIRST_UNUSED_ITEM_ID'), max($constants) + 1, "FIRST_UNUSED_ITEM_ID must be one higher than the highest fixed type ID");
	}

	public function testNoDuplicates() : void
	{
		$idTable = (new \ReflectionClass(ItemTypeIds::class))->getConstants();

		self::assertSameSize($idTable, array_unique($idTable), "Every ItemTypeID must be unique");
	}

	public function testVanillaItemsParity() : void
	{
		$reflect = new \ReflectionClass(ItemTypeIds::class);

		foreach (Utils::stringifyKeys(VanillaItems::getAll()) as $name => $item) {
			if ($item instanceof ItemBlock) {
				continue;
			}
			$expected = $item->getTypeId();
			$actual = $reflect->getConstant($name);
			self::assertNotFalse($actual, "VanillaItems::$name() does not have an ItemTypeIds constant");
			self::assertSame($expected, $actual, "VanillaItems::$name() type ID does not match ItemTypeIds::$name");
		}
	}
}
