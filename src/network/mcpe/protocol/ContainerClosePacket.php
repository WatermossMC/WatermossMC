<?php

/*
 *
 * This file part of WatermossMC.
 *
 *  __        __    _                                    __  __  ____
 *  \ \      / /_ _| |_ ___ _ __ _ __ ___   ___  ___ ___|  \/  |/ ___|
 *   \ \ /\ / / _` | __/ _ \ '__| '_ ` _ \ / _ \/ __/ __| |\/| | |
 *    \ V  V / (_| | ||  __/ |  | | | | | | (_) \__ \__ \ |  | | |___
 *     \_/\_/ \__,_|\__\___|_|  |_| |_| |_|\___/|___/___/_|  |_|\____|
 *
 * @author WatermossMC Team
 * @license Apache 2.0
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

class ContainerClosePacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_CLOSE_PACKET;

	public int $windowId;
	public int $windowType;
	public bool $server = false;

	/**
	 * @generate-create-func
	 */
	public static function create(int $windowId, int $windowType, bool $server) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->windowType = $windowType;
		$result->server = $server;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->windowId = $in->getByte();
		$this->windowType = $in->getByte();
		$this->server = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->windowId);
		$out->putByte($this->windowType);
		$out->putBool($this->server);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleContainerClose($this);
	}
}
