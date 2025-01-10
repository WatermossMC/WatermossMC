<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class ChunkPosition
{
	public function __construct(
		private int $x,
		private int $z
	) {
	}

	public function getX() : int
	{
		return $this->x;
	}

	public function getZ() : int
	{
		return $this->z;
	}

	public static function read(PacketSerializer $in) : self
	{
		$x = $in->getVarInt();
		$z = $in->getVarInt();

		return new self($x, $z);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putVarInt($this->x);
		$out->putVarInt($this->z);
	}
}
