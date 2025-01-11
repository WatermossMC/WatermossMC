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

use function base64_encode;
use function func_num_args;

final class ByteArrayTag extends ImmutableTag
{
	/** @var string */
	private $value;

	public function __construct(string $value)
	{
		self::restrictArgCount(__METHOD__, func_num_args(), 1);
		$this->value = $value;
	}

	protected function getTypeName() : string
	{
		return "ByteArray";
	}

	public function getType() : int
	{
		return NBT::TAG_ByteArray;
	}

	public static function read(NbtStreamReader $reader) : self
	{
		return new self($reader->readByteArray());
	}

	public function write(NbtStreamWriter $writer) : void
	{
		$writer->writeByteArray($this->value);
	}

	public function getValue() : string
	{
		return $this->value;
	}

	protected function stringifyValue(int $indentation) : string
	{
		return "b64:" . base64_encode($this->value);
	}
}
