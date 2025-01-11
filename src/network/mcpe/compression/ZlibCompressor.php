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

namespace watermossmc\network\mcpe\compression;

use watermossmc\network\mcpe\protocol\types\CompressionAlgorithm;
use watermossmc\utils\SingletonTrait;
use watermossmc\utils\Utils;

use function function_exists;
use function libdeflate_deflate_compress;
use function strlen;
use function zlib_decode;
use function zlib_encode;

use const ZLIB_ENCODING_RAW;

final class ZlibCompressor implements Compressor
{
	use SingletonTrait;

	public const DEFAULT_LEVEL = 7;
	public const DEFAULT_THRESHOLD = 256;
	public const DEFAULT_MAX_DECOMPRESSION_SIZE = 8 * 1024 * 1024;

	/**
	 * @see SingletonTrait::make()
	 */
	private static function make() : self
	{
		return new self(self::DEFAULT_LEVEL, self::DEFAULT_THRESHOLD, self::DEFAULT_MAX_DECOMPRESSION_SIZE);
	}

	public function __construct(
		private int $level,
		private ?int $minCompressionSize,
		private int $maxDecompressionSize
	) {
	}

	public function getCompressionThreshold() : ?int
	{
		return $this->minCompressionSize;
	}

	/**
	 * @throws DecompressionException
	 */
	public function decompress(string $payload) : string
	{
		$result = @zlib_decode($payload, $this->maxDecompressionSize);
		if ($result === false) {
			throw new DecompressionException("Failed to decompress data");
		}
		return $result;
	}

	public function compress(string $payload) : string
	{
		$compressible = $this->minCompressionSize !== null && strlen($payload) >= $this->minCompressionSize;
		$level = $compressible ? $this->level : 0;

		return function_exists('libdeflate_deflate_compress') ?
			libdeflate_deflate_compress($payload, $level) :
			Utils::assumeNotFalse(zlib_encode($payload, ZLIB_ENCODING_RAW, $level), "ZLIB compression failed");
	}

	public function getNetworkId() : int
	{
		return CompressionAlgorithm::ZLIB;
	}
}
