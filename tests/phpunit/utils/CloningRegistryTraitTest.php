<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\utils;

use PHPUnit\Framework\TestCase;

final class CloningRegistryTraitTest extends TestCase
{
	/**
	 * @phpstan-return \Generator<int, array{\Closure() : \stdClass}, void, void>
	 */
	public static function cloningRegistryMembersProvider() : \Generator
	{
		yield [function () : \stdClass { return TestCloningRegistry::TEST1(); }];
		yield [function () : \stdClass { return TestCloningRegistry::TEST2(); }];
		yield [function () : \stdClass { return TestCloningRegistry::TEST3(); }];
	}

	/**
	 * @dataProvider cloningRegistryMembersProvider
	 * @phpstan-param \Closure() : \stdClass $provider
	 */
	public function testEachMemberClone(\Closure $provider) : void
	{
		self::assertNotSame($provider(), $provider(), "Cloning registry should never return the same object twice");
	}

	public function testGetAllClone() : void
	{
		$list1 = TestCloningRegistry::getAll();
		$list2 = TestCloningRegistry::getAll();
		foreach (Utils::promoteKeys($list1) as $k => $member) {
			self::assertNotSame($member, $list2[$k], "VanillaBlocks ought to clone its members");
		}
	}
}
