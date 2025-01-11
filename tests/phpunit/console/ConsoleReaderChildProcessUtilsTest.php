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

namespace watermossmc\console;

use PHPUnit\Framework\TestCase;

use function mt_rand;
use function str_repeat;

final class ConsoleReaderChildProcessUtilsTest extends TestCase
{
	/**
	 * @phpstan-return \Generator<int, array{string}, void, void>
	 */
	public static function commandStringProvider() : \Generator
	{
		yield ["stop"];
		yield ["watermossmc:status"];
		yield [str_repeat("s", 1000)];
		yield ["time set day"];
		yield ["give \"Steve\" golden_apple"];
	}

	/**
	 * @dataProvider commandStringProvider
	 */
	public function testCreateParseSymmetry(string $input) : void
	{
		$counterCreate = $counterParse = mt_rand();
		$message = ConsoleReaderChildProcessUtils::createMessage($input, $counterCreate);
		$parsedInput = ConsoleReaderChildProcessUtils::parseMessage($message, $counterParse);

		self::assertSame($input, $parsedInput);
	}

	public function testCreateMessage() : void
	{
		$counter = 0;

		ConsoleReaderChildProcessUtils::createMessage("", $counter);
		self::assertSame(1, $counter, "createMessage should always bump the counter");
	}

	/**
	 * @phpstan-return \Generator<int, array{string, bool}, void, void>
	 */
	public static function parseMessageProvider() : \Generator
	{
		$counter = 0;
		yield [ConsoleReaderChildProcessUtils::createMessage("", $counter), true];

		yield ["", false]; //keepalive message, doesn't bump counter

		$counter = 1;
		yield [ConsoleReaderChildProcessUtils::createMessage("", $counter), false]; //mismatched counter

		$counter = 0;
		yield ["a" . ConsoleReaderChildProcessUtils::TOKEN_DELIMITER . "b", false]; //message with delimiter but not a valid IPC message
	}

	/**
	 * @dataProvider parseMessageProvider
	 */
	public static function testParseMessage(string $message, bool $valid) : void
	{
		$counter = $oldCounter = 0;

		$input = ConsoleReaderChildProcessUtils::parseMessage($message, $counter);
		if (!$valid) {
			self::assertNull($input, "Result should be null on invalid message");
			self::assertSame($oldCounter, $counter, "Counter shouldn't be bumped on invalid message");
		} else {
			self::assertNotNull($input, "This was a valid message, expected a result");
			self::assertSame($oldCounter + 1, $counter, "Counter should be bumped on valid message parse");
		}
	}
}
