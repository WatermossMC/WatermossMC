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

use watermossmc\nbt\tag\ByteArrayTag;
use watermossmc\nbt\tag\ByteTag;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\DoubleTag;
use watermossmc\nbt\tag\FloatTag;
use watermossmc\nbt\tag\IntArrayTag;
use watermossmc\nbt\tag\IntTag;
use watermossmc\nbt\tag\ListTag;
use watermossmc\nbt\tag\LongTag;
use watermossmc\nbt\tag\ShortTag;
use watermossmc\nbt\tag\StringTag;
use watermossmc\nbt\tag\Tag;

abstract class NBT
{
	public const TAG_End = 0;
	public const TAG_Byte = 1;
	public const TAG_Short = 2;
	public const TAG_Int = 3;
	public const TAG_Long = 4;
	public const TAG_Float = 5;
	public const TAG_Double = 6;
	public const TAG_ByteArray = 7;
	public const TAG_String = 8;
	public const TAG_List = 9;
	public const TAG_Compound = 10;
	public const TAG_IntArray = 11;

	/**
	 * @throws NbtDataException
	 */
	public static function createTag(int $type, NbtStreamReader $reader, ReaderTracker $tracker) : Tag
	{
		switch ($type) {
			case self::TAG_Byte:
				return ByteTag::read($reader);
			case self::TAG_Short:
				return ShortTag::read($reader);
			case self::TAG_Int:
				return IntTag::read($reader);
			case self::TAG_Long:
				return LongTag::read($reader);
			case self::TAG_Float:
				return FloatTag::read($reader);
			case self::TAG_Double:
				return DoubleTag::read($reader);
			case self::TAG_ByteArray:
				return ByteArrayTag::read($reader);
			case self::TAG_String:
				return StringTag::read($reader);
			case self::TAG_List:
				return ListTag::read($reader, $tracker);
			case self::TAG_Compound:
				return CompoundTag::read($reader, $tracker);
			case self::TAG_IntArray:
				return IntArrayTag::read($reader);
			default:
				throw new NbtDataException("Unknown NBT tag type $type");
		}
	}
}
