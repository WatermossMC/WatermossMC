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

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\CacheableNbt;

class JigsawStructureDataPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::JIGSAW_STRUCTURE_DATA_PACKET;

	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	private CacheableNbt $nbt;

	/**
	 * @generate-create-func
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $nbt
	 */
	public static function create(CacheableNbt $nbt) : self
	{
		$result = new self();
		$result->nbt = $nbt;
		return $result;
	}

	/** @phpstan-return CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	public function getNbt() : CacheableNbt
	{
		return $this->nbt;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->nbt = new CacheableNbt($in->getNbtCompoundRoot());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->put($this->nbt->getEncodedNbt());
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleJigsawStructureData($this);
	}
}
