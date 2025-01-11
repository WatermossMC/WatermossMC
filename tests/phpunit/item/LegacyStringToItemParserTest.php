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
use watermossmc\block\VanillaBlocks;

class LegacyStringToItemParserTest extends TestCase
{
	/**
	 * @return mixed[][]
	 * @phpstan-return list<array{string,Item}>
	 */
	public static function itemFromStringProvider() : array
	{
		return [
			["dye:4", VanillaItems::LAPIS_LAZULI()],
			["351", VanillaItems::INK_SAC()],
			["351:4", VanillaItems::LAPIS_LAZULI()],
			["stone:3", VanillaBlocks::DIORITE()->asItem()],
			["minecraft:string", VanillaItems::STRING()],
			["diamond_pickaxe", VanillaItems::DIAMOND_PICKAXE()]
		];
	}

	/**
	 * @dataProvider itemFromStringProvider
	 */
	public function testFromStringSingle(string $string, Item $expected) : void
	{
		$item = LegacyStringToItemParser::getInstance()->parse($string);

		self::assertTrue($item->equals($expected));
	}
}
