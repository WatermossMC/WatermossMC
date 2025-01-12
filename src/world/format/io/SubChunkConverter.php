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

use pocketmine\world\format\io\SubChunkConverter as OriginalSubChunkConverter;
use pocketmine\world\format\PalettedBlockArray;

/**
 * Wrapper class for the SubChunkConverter class from the pocketmine\world\format\io namespace.
 */
class SubChunkConverter
{
	/**
	 * Wrapper for the `convertSubChunkXZY` method.
	 *
	 * Converts sub-chunk data from XZY format to PalettedBlockArray.
	 *
	 * @param string $idArray   The ID array of the blocks.
	 * @param string $metaArray The metadata array of the blocks.
	 *
	 * @return PalettedBlockArray The converted PalettedBlockArray.
	 */
	public static function convertSubChunkXZY(string $idArray, string $metaArray) : PalettedBlockArray
	{
		return OriginalSubChunkConverter::convertSubChunkXZY($idArray, $metaArray);
	}

	/**
	 * Wrapper for the `convertSubChunkYZX` method.
	 *
	 * Converts sub-chunk data from YZX format to PalettedBlockArray.
	 *
	 * @param string $idArray   The ID array of the blocks.
	 * @param string $metaArray The metadata array of the blocks.
	 *
	 * @return PalettedBlockArray The converted PalettedBlockArray.
	 */
	public static function convertSubChunkYZX(string $idArray, string $metaArray) : PalettedBlockArray
	{
		return OriginalSubChunkConverter::convertSubChunkYZX($idArray, $metaArray);
	}

	/**
	 * Wrapper for the `convertSubChunkFromLegacyColumn` method.
	 *
	 * Converts sub-chunk data from legacy column format to PalettedBlockArray.
	 *
	 * @param string $idArray   The ID array of the blocks.
	 * @param string $metaArray The metadata array of the blocks.
	 * @param int    $yOffset   The Y offset for conversion.
	 *
	 * @return PalettedBlockArray The converted PalettedBlockArray.
	 */
	public static function convertSubChunkFromLegacyColumn(string $idArray, string $metaArray, int $yOffset) : PalettedBlockArray
	{
		return OriginalSubChunkConverter::convertSubChunkFromLegacyColumn($idArray, $metaArray, $yOffset);
	}

	/**
	 * Private constructor to prevent instantiation of the wrapper class.
	 */
	private function __construct()
	{
		// Prevent instantiation of the wrapper class.
	}
}
