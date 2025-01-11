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

final class IntTag extends ImmutableTag
{
	use IntegerishTagTrait;

	protected function min() : int
	{
		return -0x7fffffff - 1; //workaround parser bug https://bugs.php.net/bug.php?id=53934
	}

	protected function max() : int
	{
		return 0x7fffffff;
	}

	protected function getTypeName() : string
	{
		return "Int";
	}

	public function getType() : int
	{
		return NBT::TAG_Int;
	}

	public static function read(NbtStreamReader $reader) : self
	{
		return new self($reader->readInt());
	}

	public function write(NbtStreamWriter $writer) : void
	{
		$writer->writeInt($this->value);
	}
}
