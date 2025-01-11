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

final class LongTag extends ImmutableTag
{
	use IntegerishTagTrait;

	protected function min() : int
	{
		return -0x7fffffffffffffff - 1; //workaround parser bug https://bugs.php.net/bug.php?id=53934
	}

	protected function max() : int
	{
		return 0x7fffffffffffffff;
	}

	protected function getTypeName() : string
	{
		return "Long";
	}

	public function getType() : int
	{
		return NBT::TAG_Long;
	}

	public static function read(NbtStreamReader $reader) : self
	{
		return new self($reader->readLong());
	}

	public function write(NbtStreamWriter $writer) : void
	{
		$writer->writeLong($this->value);
	}
}
