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
use watermossmc\utils\Utils;

use function array_unique;
use function max;

class BlockTypeIdsTest extends TestCase
{
	public function testFirstUnused() : void
	{
		$reflect = new \ReflectionClass(BlockTypeIds::class);

		$constants = $reflect->getConstants();
		unset($constants['FIRST_UNUSED_BLOCK_ID']);

		self::assertSame($reflect->getConstant('FIRST_UNUSED_BLOCK_ID'), max($constants) + 1, "FIRST_UNUSED_BLOCK_ID must be one higher than the highest fixed type ID");
	}

	public function testNoDuplicates() : void
	{
		$idTable = (new \ReflectionClass(BlockTypeIds::class))->getConstants();

		self::assertSameSize($idTable, array_unique($idTable), "Every BlockTypeID must be unique");
	}

	public function testVanillaBlocksParity() : void
	{
		$reflect = new \ReflectionClass(BlockTypeIds::class);

		foreach (Utils::stringifyKeys(VanillaBlocks::getAll()) as $name => $block) {
			$expected = $block->getTypeId();
			$actual = $reflect->getConstant($name);
			self::assertNotFalse($actual, "VanillaBlocks::$name() does not have a BlockTypeIds constant");
			self::assertSame($expected, $actual, "VanillaBlocks::$name() does not match BlockTypeIds::$name");
		}
	}
}
