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

namespace watermossmc\command\utils;

use PHPUnit\Framework\TestCase;

class CommandStringHelperTest extends TestCase
{
	public static function parseQuoteAwareProvider() : \Generator
	{
		yield [
			'give "steve jobs" apple',
			['give', 'steve jobs', 'apple']
		];
		yield [
			'say \"escaped\"',
			['say', '"escaped"']
		];
		yield [
			'say This message contains \"escaped quotes\", which are ignored',
			['say', 'This', 'message', 'contains', '"escaped', 'quotes",', 'which', 'are', 'ignored']
		];
		yield [
			'say dontspliton"half"wayquotes',
			['say', 'dontspliton"half"wayquotes']
		];
	}

	/**
	 * @dataProvider parseQuoteAwareProvider
	 * @param string[] $expected
	 */
	public function testParseQuoteAware(string $commandLine, array $expected) : void
	{
		$actual = CommandStringHelper::parseQuoteAware($commandLine);

		self::assertSame($expected, $actual);
	}
}
