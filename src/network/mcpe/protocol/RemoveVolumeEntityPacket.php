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

class RemoveVolumeEntityPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::REMOVE_VOLUME_ENTITY_PACKET;

	private int $entityNetId;
	private int $dimension;

	/**
	 * @generate-create-func
	 */
	public static function create(int $entityNetId, int $dimension) : self
	{
		$result = new self();
		$result->entityNetId = $entityNetId;
		$result->dimension = $dimension;
		return $result;
	}

	public function getEntityNetId() : int
	{
		return $this->entityNetId;
	}

	public function getDimension() : int
	{
		return $this->dimension;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->entityNetId = $in->getUnsignedVarInt();
		$this->dimension = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->entityNetId);
		$out->putVarInt($this->dimension);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleRemoveVolumeEntity($this);
	}
}
