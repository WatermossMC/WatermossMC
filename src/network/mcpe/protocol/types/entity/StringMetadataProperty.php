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

namespace watermossmc\network\mcpe\protocol\types\entity;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class StringMetadataProperty implements MetadataProperty
{
	use GetTypeIdFromConstTrait;

	public const ID = EntityMetadataTypes::STRING;

	public function __construct(
		private string $value
	) {
	}

	public function getValue() : string
	{
		return $this->value;
	}

	public static function read(PacketSerializer $in) : self
	{
		return new self($in->getString());
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->value);
	}

	public function equals(MetadataProperty $other) : bool
	{
		return $other instanceof self && $other->value === $this->value;
	}
}
