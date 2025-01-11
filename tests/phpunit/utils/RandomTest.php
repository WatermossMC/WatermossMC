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

class RandomTest extends TestCase
{
	public function testNextSignedIntReturnsSignedInts() : void
	{
		//use a known seed which should definitely produce negatives
		$random = new Random(0);
		$negatives = false;

		for ($i = 0; $i < 100; ++$i) {
			if ($random->nextSignedInt() < 0) {
				$negatives = true;
				break;
			}
		}
		self::assertTrue($negatives);
	}
}
