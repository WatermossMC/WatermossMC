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

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\CacheableNbt;

class AvailableActorIdentifiersPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::AVAILABLE_ACTOR_IDENTIFIERS_PACKET;

	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	public CacheableNbt $identifiers;

	/**
	 * @generate-create-func
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $identifiers
	 */
	public static function create(CacheableNbt $identifiers) : self
	{
		$result = new self();
		$result->identifiers = $identifiers;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->identifiers = new CacheableNbt($in->getNbtCompoundRoot());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->put($this->identifiers->getEncodedNbt());
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAvailableActorIdentifiers($this);
	}
}
