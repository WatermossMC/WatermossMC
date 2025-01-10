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

use watermossmc\network\mcpe\protocol\PacketDecodeException;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\CacheableNbt;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class CompoundTagMetadataProperty implements MetadataProperty
{
	use GetTypeIdFromConstTrait;

	public const ID = EntityMetadataTypes::COMPOUND_TAG;

	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	private CacheableNbt $value;

	/**
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $value
	 */
	public function __construct(CacheableNbt $value)
	{
		$this->value = clone $value;
	}

	/**
	 * @phpstan-return CacheableNbt<\watermossmc\nbt\tag\CompoundTag>
	 */
	public function getValue() : CacheableNbt
	{
		return clone $this->value;
	}

	public function equals(MetadataProperty $other) : bool
	{
		return $other instanceof self && $other->value->getRoot()->equals($this->value->getRoot());
	}

	/**
	 * @throws PacketDecodeException
	 */
	public static function read(PacketSerializer $in) : self
	{
		return new self(new CacheableNbt($in->getNbtCompoundRoot()));
	}

	public function write(PacketSerializer $out) : void
	{
		$out->put($this->value->getEncodedNbt());
	}
}
