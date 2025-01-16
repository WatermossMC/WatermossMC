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

use watermossmc\utils\Binary;
use watermossmc\utils\BinaryStream;
use watermossmc\world\format\Chunk;

use watermossmc\world\format\SubChunk;

use function array_values;
use function count;
use function pack;
use function strlen;
use function unpack;

/**
 * This class provides a serializer used for transmitting chunks between threads.
 * The serialization format **is not intended for permanent storage** and may change without warning.
 */
final class FastChunkSerializer
{
	private const FLAG_POPULATED = 1 << 1;

	private function __construct()
	{
		//NOOP
	}

	private static function serializePalettedArray(BinaryStream $stream, PalettedBlockArray $array) : void
	{
		$wordArray = $array->getWordArray();
		$palette = $array->getPalette();

		$stream->putByte($array->getBitsPerBlock());
		$stream->put($wordArray);
		$serialPalette = pack("L*", ...$palette);
		$stream->putInt(strlen($serialPalette));
		$stream->put($serialPalette);
	}

	/**
	 * Fast-serializes the chunk for passing between threads
	 * TODO: tiles and entities
	 */
	public static function serializeTerrain(Chunk $chunk) : string
	{
		$stream = new BinaryStream();
		$stream->putByte(
			($chunk->isPopulated() ? self::FLAG_POPULATED : 0)
		);

		//subchunks
		$subChunks = $chunk->getSubChunks();
		$count = count($subChunks);
		$stream->putByte($count);

		foreach ($subChunks as $y => $subChunk) {
			$stream->putByte($y);
			$stream->putInt($subChunk->getEmptyBlockId());
			$layers = $subChunk->getBlockLayers();
			$stream->putByte(count($layers));
			foreach ($layers as $blocks) {
				self::serializePalettedArray($stream, $blocks);
			}
			self::serializePalettedArray($stream, $subChunk->getBiomeArray());

		}

		return $stream->getBuffer();
	}

	private static function deserializePalettedArray(BinaryStream $stream) : PalettedBlockArray
	{
		$bitsPerBlock = $stream->getByte();
		$words = $stream->get(PalettedBlockArray::getExpectedWordArraySize($bitsPerBlock));
		/** @var int[] $unpackedPalette */
		$unpackedPalette = unpack("L*", $stream->get($stream->getInt())); //unpack() will never fail here
		$palette = array_values($unpackedPalette);

		return PalettedBlockArray::fromData($bitsPerBlock, $words, $palette);
	}

	/**
	 * Deserializes a fast-serialized chunk
	 */
	public static function deserializeTerrain(string $data) : Chunk
	{
		$stream = new BinaryStream($data);

		$flags = $stream->getByte();
		$terrainPopulated = (bool) ($flags & self::FLAG_POPULATED);

		$subChunks = [];

		$count = $stream->getByte();
		for ($subCount = 0; $subCount < $count; ++$subCount) {
			$y = Binary::signByte($stream->getByte());
			$airBlockId = $stream->getInt();

			/** @var PalettedBlockArray[] $layers */
			$layers = [];
			for ($i = 0, $layerCount = $stream->getByte(); $i < $layerCount; ++$i) {
				$layers[] = self::deserializePalettedArray($stream);
			}
			$biomeArray = self::deserializePalettedArray($stream);
			$subChunks[$y] = new SubChunk($airBlockId, $layers, $biomeArray);
		}

		return new Chunk($subChunks, $terrainPopulated);
	}
}
