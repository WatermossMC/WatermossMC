<?php

/*
 * __        __    _                                    __  __  ____
 * \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *  \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *   \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *    \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * WatermossMC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author WatermossMC Team
 * @link https://github.com/watermossmc/WatermossMC
 */

declare (strict_types=1);

namespace watermossmc\world;

use RuntimeException;
use watermossmc\binary\Binary;
use watermossmc\binary\McpeBinary;
use watermossmc\block\BlockRegistry;
use watermossmc\block\BlockRuntimeIdConverter;

final class SubChunk
{
    public const SIZE = 4096;

    /** @var \SplFixedArray<int> */
    private \SplFixedArray $blocks;

    public function __construct()
    {
        $this->blocks = new \SplFixedArray(self::SIZE);
        for ($i = 0; $i < self::SIZE; $i++) {
            $this->blocks[$i] = 0;
        }
    }

    public function setBlock(int $x, int $y, int $z, int $id): void
    {
        $index = $y << 8 | $z << 4 | $x;
        $this->blocks[$index] = $id;
    }

    public function getBlock(int $x, int $y, int $z): int
    {
        $index = $y << 8 | $z << 4 | $x;
        return $this->blocks[$index] ?? 0;
    }

    public function encode(
		BlockRuntimeIdConverter $converter
	): string {
		$paletteData = $this->createPalette($converter);

		$palette = $paletteData['palette'];
		$indexes = $paletteData['indexes'];

		$rawBits = max(1, (int) ceil(log(count($palette), 2)));

		$validBits = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 8, 8 => 8];
		$bits = $rawBits > 8 ? 16 : $validBits[$rawBits];

		$out = '';

		$out .= Binary::writeByte(8);
		$out .= Binary::writeByte(1);
		$out .= Binary::writeByte($bits << 1);

		$out .= $this->encodeIndexes(
			$indexes,
			$bits
		);

		$out .= McpeBinary::writeSignedVarInt(
			count($palette)
		);

		foreach ($palette as $runtimeId) {
			$out .= McpeBinary::writeUnsignedVarInt(
				$runtimeId
			);
		}

		return $out;
	}

    /**
     * @param array<int, int> $palette
     */
    private function encodeBlocks(array $palette, int $bits): string
    {
        $indexes = array_flip($palette);
        $buffer = '';
        $value = 0;
        $bitPos = 0;
        foreach ($this->blocks as $block) {
            $value |= $indexes[$block] << $bitPos;
            $bitPos += $bits;
            if ($bitPos >= 32) {
                $buffer .= Binary::writeInt($value);
                $value = 0;
                $bitPos = 0;
            }
        }
        if ($bitPos > 0) {
            $buffer .= Binary::writeInt($value);
        }
        return $buffer;
    }

    public function exportBinary(): string
    {
        return pack('v*', ...$this->blocks);
    }

    public static function fromBinary(string $data): self
    {
        if (\strlen($data) !== self::SIZE * 2) {
            throw new RuntimeException('Invalid subchunk binary size');
        }
        $values = unpack('v' . self::SIZE, $data);
        if ($values === false) {
            throw new RuntimeException('Failed to decode subchunk binary');
        }
        $subChunk = new self();
        $subChunk->blocks = \SplFixedArray::fromArray(array_values($values), false);
        return $subChunk;
    }

	/**
	 * @return \SplFixedArray<int>
	 */
	public function getBlocks(): \SplFixedArray
	{
    	return $this->blocks;
	}

	/**
	 * @return array{
	 *     palette: int[],
	 *     indexes: int[]
	 * }
 	 */
	public function createPalette(
		BlockRuntimeIdConverter $converter
	): array {
		$palette = [];
		$paletteMap = [];
		$indexes = [];

		foreach ($this->blocks as $index => $blockId) {
			$block = BlockRegistry::get($blockId);

			if ($block === null) {
				throw new RuntimeException(
					"Unknown block ID: {$blockId}"
				);
			}

			$runtimeId = $converter->toRuntimeId($block);

			if (!isset($paletteMap[$runtimeId])) {
				$paletteMap[$runtimeId] = count($palette);
				$palette[] = $runtimeId;
			}

			$indexes[$index] = $paletteMap[$runtimeId];
		}

		return [
			'palette' => $palette,
			'indexes' => $indexes,
		];
	}

	/**
 	 * @param int[] $indexes
 	 */
	private function encodeIndexes(
		array $indexes,
		int $bits
	): string {
		$valuesPerWord = intdiv(32, $bits);

		if ($valuesPerWord <= 0) {
			throw new RuntimeException(
				"Invalid bits per block: {$bits}"
			);
		}

		$out = '';

		$count = count($indexes);
		$words = (int) ceil(
			$count / $valuesPerWord
		);

		for ($wordIndex = 0; $wordIndex < $words; $wordIndex++) {
			$value = 0;

			for ($i = 0; $i < $valuesPerWord; $i++) {
				$index = $wordIndex * $valuesPerWord + $i;

				if ($index >= $count) {
					break;
				}

				$value |= $indexes[$index] << ($i * $bits);
			}

			$out .= Binary::writeInt($value);
		}

		return $out;
	}
}
