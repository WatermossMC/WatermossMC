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
