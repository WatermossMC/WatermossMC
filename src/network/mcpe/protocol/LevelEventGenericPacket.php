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

use watermossmc\nbt\NBT;
use watermossmc\nbt\NbtDataException;
use watermossmc\nbt\tag\Tag;
use watermossmc\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

class LevelEventGenericPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::LEVEL_EVENT_GENERIC_PACKET;

	private int $eventId;
	private Tag $eventData;

	/**
	 * @generate-create-func
	 */
	public static function create(int $eventId, Tag $eventData) : self
	{
		$result = new self();
		$result->eventId = $eventId;
		$result->eventData = $eventData;
		return $result;
	}

	public function getEventId() : int
	{
		return $this->eventId;
	}

	public function getEventData() : Tag
	{
		return $this->eventData;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->eventId = $in->getVarInt();
		$offset = $in->getOffset();
		try {
			$this->eventData = (new NetworkNbtSerializer())->readHeadless($in->getBuffer(), NBT::TAG_Compound, $offset);
		} catch (NbtDataException $e) {
			throw PacketDecodeException::wrap($e);
		}
		$in->setOffset($offset);
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->eventId);
		$out->put((new NetworkNbtSerializer())->writeHeadless($this->eventData));
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleLevelEventGeneric($this);
	}
}
