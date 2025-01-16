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

namespace watermossmc\world\format\io;



use function chr;
use function ord;
use function str_repeat;
use function strlen;

class ChunkUtils
{
	/**
	 * Converts pre-MCPE-1.0 biome color array to biome ID array.
	 *
	 * @param int[] $array of biome color values
	 * @phpstan-param list<int> $array
	 */
	public static function convertBiomeColors(array $array) : string
	{
		$result = str_repeat("\x00", 256);
		foreach ($array as $i => $color) {
			$result[$i] = chr(($color >> 24) & 0xff);
		}
		return $result;
	}

	/**
	 * Converts 2D biomes into a 3D biome palette. This palette can then be cloned for every subchunk.
	 */
	public static function extrapolate3DBiomes(string $biomes2d) : PalettedBlockArray
	{
		if (strlen($biomes2d) !== 256) {
			throw new \InvalidArgumentException("Biome array is expected to be exactly 256 bytes");
		}
		$biomePalette = new PalettedBlockArray(ord($biomes2d[0]));
		for ($x = 0; $x < 16; ++$x) {
			for ($z = 0; $z < 16; ++$z) {
				$biomeId = ord($biomes2d[($z << 4) | $x]);
				for ($y = 0; $y < 16; ++$y) {
					$biomePalette->set($x, $y, $z, $biomeId);
				}
			}
		}

		return $biomePalette;
	}
}
