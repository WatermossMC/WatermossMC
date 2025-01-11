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

namespace watermossmc\nbt;

use watermossmc\utils\BinaryDataException;

/**
 * @internal
 */
interface NbtStreamReader
{
	/**
	 * @throws BinaryDataException
	 */
	public function readByte() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readSignedByte() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readShort() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readSignedShort() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readInt() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readLong() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readFloat() : float;

	/**
	 * @throws BinaryDataException
	 */
	public function readDouble() : float;

	/**
	 * @throws BinaryDataException
	 */
	public function readByteArray() : string;

	/**
	 * @throws BinaryDataException
	 */
	public function readString() : string;

	/**
	 * @return int[]
	 * @phpstan-return list<int>
	 * @throws BinaryDataException
	 */
	public function readIntArray() : array;
}
