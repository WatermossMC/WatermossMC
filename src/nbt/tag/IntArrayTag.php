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

use function array_values;
use function assert;
use function func_num_args;
use function implode;
use function is_int;

final class IntArrayTag extends ImmutableTag
{
	/**
	 * @var int[]
	 * @phpstan-var list<int>
	 */
	private $value;

	/**
	 * @param int[] $value
	 * @phpstan-param list<int> $value
	 */
	public function __construct(array $value)
	{
		self::restrictArgCount(__METHOD__, func_num_args(), 1);
		assert((function () use (&$value) : bool {
			foreach ($value as $v) {
				if (!is_int($v)) {
					return false;
				}
			}

			return true;
		})());

		$this->value = array_values($value);
	}

	protected function getTypeName() : string
	{
		return "IntArray";
	}

	public function getType() : int
	{
		return NBT::TAG_IntArray;
	}

	public static function read(NbtStreamReader $reader) : self
	{
		return new self($reader->readIntArray());
	}

	public function write(NbtStreamWriter $writer) : void
	{
		$writer->writeIntArray($this->value);
	}

	protected function stringifyValue(int $indentation) : string
	{
		return "[" . implode(",", $this->value) . "]";
	}

	/**
	 * @return int[]
	 * @phpstan-return list<int>
	 */
	public function getValue() : array
	{
		return $this->value;
	}
}
