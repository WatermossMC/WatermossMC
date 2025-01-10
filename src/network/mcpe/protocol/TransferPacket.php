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

class TransferPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::TRANSFER_PACKET;

	public string $address;
	public int $port = 19132;
	public bool $reloadWorld;

	/**
	 * @generate-create-func
	 */
	public static function create(string $address, int $port, bool $reloadWorld) : self
	{
		$result = new self();
		$result->address = $address;
		$result->port = $port;
		$result->reloadWorld = $reloadWorld;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->address = $in->getString();
		$this->port = $in->getLShort();
		$this->reloadWorld = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->address);
		$out->putLShort($this->port);
		$out->putBool($this->reloadWorld);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleTransfer($this);
	}
}
