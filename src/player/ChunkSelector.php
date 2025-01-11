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

namespace watermossmc\player;

use watermossmc\world\World;

use const M_SQRT2;

//TODO: turn this into an interface?
final class ChunkSelector
{
	/**
	 * @return \Generator|int[]
	 * @phpstan-return \Generator<int, int, void, void>
	 */
	public function selectChunks(int $radius, int $centerX, int $centerZ) : \Generator
	{
		for ($subRadius = 0; $subRadius < $radius; $subRadius++) {
			$subRadiusSquared = $subRadius ** 2;
			$nextSubRadiusSquared = ($subRadius + 1) ** 2;
			$minX = (int) ($subRadius / M_SQRT2);

			$lastZ = 0;

			for ($x = $subRadius; $x >= $minX; --$x) {
				for ($z = $lastZ; $z <= $x; ++$z) {
					$distanceSquared = ($x ** 2 + $z ** 2);
					if ($distanceSquared < $subRadiusSquared) {
						continue;
					} elseif ($distanceSquared >= $nextSubRadiusSquared) {
						break; //skip to next X
					}

					$lastZ = $z;
					//If the chunk is in the radius, others at the same offsets in different quadrants are also guaranteed to be.

					/* Top right quadrant */
					yield $subRadius => World::chunkHash($centerX + $x, $centerZ + $z);
					/* Top left quadrant */
					yield $subRadius => World::chunkHash($centerX - $x - 1, $centerZ + $z);
					/* Bottom right quadrant */
					yield $subRadius => World::chunkHash($centerX + $x, $centerZ - $z - 1);
					/* Bottom left quadrant */
					yield $subRadius => World::chunkHash($centerX - $x - 1, $centerZ - $z - 1);

					if ($x !== $z) {
						/* Top right quadrant mirror */
						yield $subRadius => World::chunkHash($centerX + $z, $centerZ + $x);
						/* Top left quadrant mirror */
						yield $subRadius => World::chunkHash($centerX - $z - 1, $centerZ + $x);
						/* Bottom right quadrant mirror */
						yield $subRadius => World::chunkHash($centerX + $z, $centerZ - $x - 1);
						/* Bottom left quadrant mirror */
						yield $subRadius => World::chunkHash($centerX - $z - 1, $centerZ - $x - 1);
					}
				}
			}
		}
	}
}
