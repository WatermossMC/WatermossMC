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

/**
 * @internal
 */
interface NbtStreamWriter
{
	public function writeByte(int $v) : void;

	public function writeShort(int $v) : void;

	public function writeInt(int $v) : void;

	public function writeLong(int $v) : void;

	public function writeFloat(float $v) : void;

	public function writeDouble(float $v) : void;

	public function writeByteArray(string $v) : void;

	/**
	 * @throws \InvalidArgumentException if the string is too long
	 */
	public function writeString(string $v) : void;

	/**
	 * @param int[] $array
	 * @phpstan-param list<int> $array
	 */
	public function writeIntArray(array $array) : void;
}
