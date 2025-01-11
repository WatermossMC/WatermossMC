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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\color\Color;
use watermossmc\network\mcpe\protocol\PacketDecodeException;
use watermossmc\utils\Binary;
use watermossmc\utils\BinaryDataException;
use watermossmc\utils\BinaryStream;

use function count;

final class MapImage
{
	//these limits are enforced in the protocol in 1.20.0
	public const MAX_HEIGHT = 128;
	public const MAX_WIDTH = 128;

	private int $width;
	private int $height;
	/**
	 * @var Color[][]
	 * @phpstan-var list<list<Color>>
	 */
	private array $pixels;
	private ?string $encodedPixelCache = null;

	/**
	 * @param Color[][] $pixels
	 * @phpstan-param list<list<Color>> $pixels
	 */
	public function __construct(array $pixels)
	{
		$rowLength = null;
		foreach ($pixels as $row) {
			if ($rowLength === null) {
				$rowLength = count($row);
			} elseif (count($row) !== $rowLength) {
				throw new \InvalidArgumentException("All rows must have the same number of pixels");
			}
		}
		if ($rowLength === null) {
			throw new \InvalidArgumentException("No pixels provided");
		}
		if ($rowLength > self::MAX_WIDTH) {
			throw new \InvalidArgumentException("Image width must be at most " . self::MAX_WIDTH . " pixels wide");
		}
		if (count($pixels) > self::MAX_HEIGHT) {
			throw new \InvalidArgumentException("Image height must be at most " . self::MAX_HEIGHT . " pixels tall");
		}
		$this->height = count($pixels);
		$this->width = $rowLength;
		$this->pixels = $pixels;
	}

	public function getWidth() : int
	{
		return $this->width;
	}

	public function getHeight() : int
	{
		return $this->height;
	}

	/**
	 * @return Color[][]
	 * @phpstan-return list<list<Color>>
	 */
	public function getPixels() : array
	{
		return $this->pixels;
	}

	public function encode(BinaryStream $output) : void
	{
		if ($this->encodedPixelCache === null) {
			$serializer = new BinaryStream();
			for ($y = 0; $y < $this->height; ++$y) {
				for ($x = 0; $x < $this->width; ++$x) {
					//if mojang had any sense this would just be a regular LE int
					$serializer->putUnsignedVarInt(Binary::flipIntEndianness($this->pixels[$y][$x]->toRGBA()));
				}
			}
			$this->encodedPixelCache = $serializer->getBuffer();
		}

		$output->put($this->encodedPixelCache);
	}

	/**
	 * @throws PacketDecodeException
	 * @throws BinaryDataException
	 */
	public static function decode(BinaryStream $input, int $height, int $width) : self
	{
		if ($width > self::MAX_WIDTH) {
			throw new PacketDecodeException("Image width must be at most " . self::MAX_WIDTH . " pixels wide");
		}
		if ($height > self::MAX_HEIGHT) {
			throw new PacketDecodeException("Image height must be at most " . self::MAX_HEIGHT . " pixels tall");
		}
		$pixels = [];

		for ($y = 0; $y < $height; ++$y) {
			$row = [];
			for ($x = 0; $x < $width; ++$x) {
				$row[] = Color::fromRGBA(Binary::flipIntEndianness($input->getUnsignedVarInt()));
			}
			$pixels[] = $row;
		}

		return new self($pixels);
	}
}
