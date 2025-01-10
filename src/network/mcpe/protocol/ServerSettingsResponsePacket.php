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

class ServerSettingsResponsePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVER_SETTINGS_RESPONSE_PACKET;

	public int $formId;
	public string $formData; //json

	/**
	 * @generate-create-func
	 */
	public static function create(int $formId, string $formData) : self
	{
		$result = new self();
		$result->formId = $formId;
		$result->formData = $formData;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->formId = $in->getUnsignedVarInt();
		$this->formData = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->formId);
		$out->putString($this->formData);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleServerSettingsResponse($this);
	}
}
