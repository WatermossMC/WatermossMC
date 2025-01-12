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

use pocketmine\world\format\PalettedBlockArray;

/**
 * Wrapper class for the PalettedBlockArray class from the ext-chunkutils2 extension.
 */
class PalettedBlockArray
{
	/** @var PalettedBlockArray */
	private $palettedBlockArray;

	/**
	 * PalettedBlockArray constructor.
	 *
	 * @param int $fillEntry The entry to fill in the PalettedBlockArray.
	 */
	public function __construct(int $fillEntry)
	{
		// Create an instance of the original PalettedBlockArray class
		$this->palettedBlockArray = new PalettedBlockArray($fillEntry);
	}

	/**
	 * Wraps the static `fromData` method.
	 *
	 * @param int    $bitsPerBlock The number of bits per block.
	 * @param string $wordArray    The word array data.
	 * @param int[]  $palette      The palette of block IDs.
	 *
	 * @return PalettedBlockArray A new instance of PalettedBlockArray.
	 */
	public static function fromData(int $bitsPerBlock, string $wordArray, array $palette) : PalettedBlockArray
	{
		$palettedBlockArray = PalettedBlockArray::fromData($bitsPerBlock, $wordArray, $palette);
		return new PalettedBlockArray($palettedBlockArray);
	}

	/**
	 * Wrapper for the `getWordArray` method.
	 *
	 * @return string The word array.
	 */
	public function getWordArray() : string
	{
		return $this->palettedBlockArray->getWordArray();
	}

	/**
	 * Wrapper for the `getPalette` method.
	 *
	 * @return int[] An array of block IDs (palette).
	 */
	public function getPalette() : array
	{
		return $this->palettedBlockArray->getPalette();
	}

	/**
	 * Wrapper for the `setPalette` method.
	 *
	 * @param int[] $palette An array of block IDs to set as the new palette.
	 */
	public function setPalette(array $palette) : void
	{
		$this->palettedBlockArray->setPalette($palette);
	}

	/**
	 * Wrapper for the `getMaxPaletteSize` method.
	 *
	 * @return int The maximum size of the palette.
	 */
	public function getMaxPaletteSize() : int
	{
		return $this->palettedBlockArray->getMaxPaletteSize();
	}

	/**
	 * Wrapper for the `getBitsPerBlock` method.
	 *
	 * @return int The number of bits per block.
	 */
	public function getBitsPerBlock() : int
	{
		return $this->palettedBlockArray->getBitsPerBlock();
	}

	/**
	 * Wrapper for the `get` method.
	 *
	 * @param int $x The x-coordinate.
	 * @param int $y The y-coordinate.
	 * @param int $z The z-coordinate.
	 *
	 * @return int The block ID at the specified coordinates.
	 */
	public function get(int $x, int $y, int $z) : int
	{
		return $this->palettedBlockArray->get($x, $y, $z);
	}

	/**
	 * Wrapper for the `set` method.
	 *
	 * @param int $x   The x-coordinate.
	 * @param int $y   The y-coordinate.
	 * @param int $z   The z-coordinate.
	 * @param int $val The block ID to set at the specified coordinates.
	 */
	public function set(int $x, int $y, int $z, int $val) : void
	{
		$this->palettedBlockArray->set($x, $y, $z, $val);
	}

	/**
	 * Wrapper for the `replaceAll` method.
	 *
	 * @param int $oldVal The old block ID to replace.
	 * @param int $newVal The new block ID to replace with.
	 */
	public function replaceAll(int $oldVal, int $newVal) : void
	{
		$this->palettedBlockArray->replaceAll($oldVal, $newVal);
	}

	/**
	 * Wrapper for the `collectGarbage` method.
	 *
	 * @param bool $force Whether to force garbage collection.
	 */
	public function collectGarbage(bool $force = false) : void
	{
		$this->palettedBlockArray->collectGarbage($force);
	}

	/**
	 * Wraps the static `getExpectedWordArraySize` method.
	 *
	 * @param int $bitsPerBlock The number of bits per block.
	 *
	 * @return int The expected word array size.
	 */
	public static function getExpectedWordArraySize(int $bitsPerBlock) : int
	{
		return PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock);
	}
}
