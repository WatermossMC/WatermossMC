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

namespace watermossmc\world\format;

use PHPUnit\Framework\TestCase;

class ChunkTest extends TestCase
{
	public function testClone() : void
	{
		$chunk = new Chunk([], false);
		$chunk->setBlockStateId(0, 0, 0, 1);
		$chunk->setBiomeId(0, 0, 0, 1);
		$chunk->setHeightMap(0, 0, 1);

		$chunk2 = clone $chunk;
		$chunk2->setBlockStateId(0, 0, 0, 2);
		$chunk2->setBiomeId(0, 0, 0, 2);
		$chunk2->setHeightMap(0, 0, 2);

		self::assertNotSame($chunk->getBlockStateId(0, 0, 0), $chunk2->getBlockStateId(0, 0, 0));
		self::assertNotSame($chunk->getBiomeId(0, 0, 0), $chunk2->getBiomeId(0, 0, 0));
		self::assertNotSame($chunk->getHeightMap(0, 0), $chunk2->getHeightMap(0, 0));
	}
}
