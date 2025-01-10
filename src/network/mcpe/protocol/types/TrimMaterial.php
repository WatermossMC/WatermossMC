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

final class TrimMaterial
{
	public function __construct(
		private string $materialId,
		private string $color,
		private string $itemId
	) {
	}

	public function getMaterialId() : string
	{
		return $this->materialId;
	}

	public function getColor() : string
	{
		return $this->color;
	}

	public function getItemId() : string
	{
		return $this->itemId;
	}

	public static function read(PacketSerializer $in) : self
	{
		$materialId = $in->getString();
		$color = $in->getString();
		$itemId = $in->getString();
		return new self($materialId, $color, $itemId);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->materialId);
		$out->putString($this->color);
		$out->putString($this->itemId);
	}
}
