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

namespace watermossmc\block\utils;

use PHPUnit\Framework\TestCase;

class SignTextTest extends TestCase
{
	public function testConstructorOmitLines() : void
	{
		$text = new SignText([1 => "test"]);
		self::assertSame("", $text->getLine(0));
		self::assertSame("test", $text->getLine(1));
		self::assertSame("", $text->getLine(2));
		self::assertSame("", $text->getLine(3));
	}
}
