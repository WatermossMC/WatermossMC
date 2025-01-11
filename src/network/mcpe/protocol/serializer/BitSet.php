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

namespace watermossmc\network\mcpe\protocol\serializer;

use function array_pad;
use function array_slice;
use function array_values;
use function count;
use function intdiv;

use const PHP_INT_SIZE;

class BitSet
{
	private const INT_BITS = PHP_INT_SIZE * 8;
	private const SHIFT = 7;

	/**
	 * @param int[] $parts
	 */
	public function __construct(
		private readonly int $length,
		private array $parts = []
	) {
		$expectedPartsCount = self::getExpectedPartsCount($length);
		$partsCount = count($parts);

		if ($partsCount > $expectedPartsCount) {
			throw new \InvalidArgumentException("Too many parts");
		} elseif ($partsCount < $expectedPartsCount) {
			$parts = array_pad($parts, $expectedPartsCount, 0);
		}

		$this->parts = array_values($parts);
	}

	public function get(int $index) : bool
	{
		[$partIndex, $bitIndex] = $this->getPartIndex($index);

		return ($this->parts[$partIndex] & (1 << $bitIndex)) !== 0;
	}

	public function set(int $index, bool $value) : void
	{
		[$partIndex, $bitIndex] = $this->getPartIndex($index);

		if ($value) {
			$this->parts[$partIndex] |= 1 << $bitIndex;
		} else {
			$this->parts[$partIndex] &= ~(1 << $bitIndex);
		}
	}

	/**
	 * Returns the part index and the bit index within that part for a given bit index.
	 *
	 * @return array{int, int}
	 */
	private function getPartIndex(int $index) : array
	{
		if ($index < 0 || $index >= $this->length) {
			throw new \InvalidArgumentException("Index out of bounds");
		}

		return [
			intdiv($index, self::INT_BITS),
			$index % self::INT_BITS
		];
	}

	/**
	 * @internal
	 */
	public function getPartsCount() : int
	{
		return count($this->parts);
	}

	private static function getExpectedPartsCount(int $length) : int
	{
		return intdiv($length + self::INT_BITS - 1, self::INT_BITS);
	}

	public static function read(PacketSerializer $in, int $length) : self
	{
		$result = [0];

		$currentIndex = 0;
		$currentShift = 0;

		for ($i = 0; $i < $length; $i += self::SHIFT) {
			$b = $in->getByte();
			$bits = $b & 0x7f;

			$result[$currentIndex] |= $bits << $currentShift; //extra bits will be discarded
			$nextShift = $currentShift + self::SHIFT;
			if ($nextShift >= self::INT_BITS) {
				$nextShift -= self::INT_BITS;
				$rightShift = self::SHIFT - $nextShift;
				$result[++$currentIndex] = $bits >> $rightShift;
			}
			$currentShift = $nextShift;

			if (($b & 0x80) === 0) {
				return new self($length, array_slice($result, 0, self::getExpectedPartsCount($length)));
			}
		}

		return new self($length, array_slice($result, 0, self::getExpectedPartsCount($length)));
	}

	public function write(PacketSerializer $out) : void
	{
		$parts = $this->parts;
		$length = $this->length;

		$currentIndex = 0;
		$currentShift = 0;

		for ($i = 0; $i < $length; $i += self::SHIFT) {
			$bits = $parts[$currentIndex] >> $currentShift;
			$nextShift = $currentShift + self::SHIFT;
			if ($nextShift >= self::INT_BITS) {
				$nextShift -= self::INT_BITS;
				$bits |= ($parts[++$currentIndex] ?? 0) << (self::SHIFT - $nextShift);
			}
			$currentShift = $nextShift;

			$last = $i + self::SHIFT >= $length;
			$bits |= $last ? 0 : 0x80;

			$out->putByte($bits);
			if ($last) {
				break;
			}
		}
	}

	public function getLength() : int
	{
		return $this->length;
	}
}
