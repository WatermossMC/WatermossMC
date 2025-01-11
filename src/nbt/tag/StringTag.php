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

namespace watermossmc\nbt\tag;

use watermossmc\nbt\InvalidTagValueException;
use watermossmc\nbt\NBT;
use watermossmc\nbt\NbtStreamReader;
use watermossmc\nbt\NbtStreamWriter;

use function func_num_args;
use function strlen;

final class StringTag extends ImmutableTag
{
	/** @var string */
	private $value;

	public function __construct(string $value)
	{
		self::restrictArgCount(__METHOD__, func_num_args(), 1);
		if (strlen($value) > 32767) {
			throw new InvalidTagValueException("StringTag cannot hold more than 32767 bytes, got string of length " . strlen($value));
		}
		$this->value = $value;
	}

	protected function getTypeName() : string
	{
		return "String";
	}

	public function getType() : int
	{
		return NBT::TAG_String;
	}

	public static function read(NbtStreamReader $reader) : self
	{
		return new self($reader->readString());
	}

	public function write(NbtStreamWriter $writer) : void
	{
		$writer->writeString($this->value);
	}

	public function getValue() : string
	{
		return $this->value;
	}

	protected function stringifyValue(int $indentation) : string
	{
		return '"' . $this->value . '"';
	}
}
