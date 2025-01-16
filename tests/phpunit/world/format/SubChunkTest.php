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
use watermossmc\data\bedrock\BiomeIds;

class SubChunkTest extends TestCase
{
	/**
	 * Test that a cloned SubChunk instance doesn't influence the original
	 */
	public function testClone() : void
	{
		$sub1 = new SubChunk(0, [], new PalettedBlockArray(BiomeIds::OCEAN));

		$sub1->setBlockStateId(0, 0, 0, 1);
		$sub1->getBlock\pocketmine\worldormat\LightArray()->set(0, 0, 0, 1);
		$sub1->getBlockSky\pocketmine\worldormat\LightArray()->set(0, 0, 0, 1);

		$sub2 = clone $sub1;

		$sub2->setBlockStateId(0, 0, 0, 2);
		$sub2->getBlock\pocketmine\worldormat\LightArray()->set(0, 0, 0, 2);
		$sub2->getBlockSky\pocketmine\worldormat\LightArray()->set(0, 0, 0, 2);

		self::assertNotSame($sub1->getBlockStateId(0, 0, 0), $sub2->getBlockStateId(0, 0, 0));
		self::assertNotSame($sub1->getBlock\pocketmine\worldormat\LightArray()->get(0, 0, 0), $sub2->getBlock\pocketmine\worldormat\LightArray()->get(0, 0, 0));
		self::assertNotSame($sub1->getBlockSky\pocketmine\worldormat\LightArray()->get(0, 0, 0), $sub2->getBlockSky\pocketmine\worldormat\LightArray()->get(0, 0, 0));
	}
}
