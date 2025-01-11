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

use watermossmc\nbt\NBT;
use watermossmc\nbt\NbtStreamReader;
use watermossmc\nbt\NbtStreamWriter;
use watermossmc\utils\Binary;

use function func_num_args;

final class FloatTag extends ImmutableTag
{
	/** @var float */
	private $value;

	public function __construct(float $value)
	{
		self::restrictArgCount(__METHOD__, func_num_args(), 1);
		$this->value = $value;
	}

	protected function getTypeName() : string
	{
		return "Float";
	}

	public function getType() : int
	{
		return NBT::TAG_Float;
	}

	public static function read(NbtStreamReader $reader) : self
	{
		return new self($reader->readFloat());
	}

	public function write(NbtStreamWriter $writer) : void
	{
		$writer->writeFloat($this->value);
	}

	public function getValue() : float
	{
		return $this->value;
	}

	protected function stringifyValue(int $indentation) : string
	{
		return (string) $this->value;
	}

	public function equals(Tag $that) : bool
	{
		//the values of TAG_Float are represented in 32 bits (single precision), so we don't want extra precision given
		//by 64-bit in-memory representation to break comparison (e.g. 0.3 != decode(encode(0.3)))
		//this intentionally truncates our value so that it compares as valid
		return $that instanceof $this && Binary::writeFloat($this->value) === Binary::writeFloat($that->value);
	}
}
