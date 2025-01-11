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

interface Compressor
{
	/**
	 * @throws DecompressionException
	 */
	public function decompress(string $payload) : string;

	public function compress(string $payload) : string;

	/**
	 * Returns the canonical ID of this compressor, used to tell the remote end how to decompress a packet compressed
	 * with this compressor.
	 *
	 * @return CompressionAlgorithm::*
	 */
	public function getNetworkId() : int;

	/**
	 * Returns the minimum size of packet batch that the compressor will attempt to compress.
	 *
	 * The compressor's output **MUST** still be valid input for the decompressor even if the compressor input is
	 * below this threshold.
	 * However, it may choose to use a cheaper compression option (e.g. zlib level 0, which simply wraps the data and
	 * doesn't attempt to compress it) to avoid wasting CPU time.
	 */
	public function getCompressionThreshold() : ?int;
}
