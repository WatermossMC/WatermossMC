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

class DebugInfoPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::DEBUG_INFO_PACKET;

	private int $actorUniqueId;
	private string $data;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorUniqueId, string $data) : self
	{
		$result = new self();
		$result->actorUniqueId = $actorUniqueId;
		$result->data = $data;
		return $result;
	}

	public function getActorUniqueId() : int
	{
		return $this->actorUniqueId;
	}

	public function getData() : string
	{
		return $this->data;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorUniqueId = $in->getActorUniqueId();
		$this->data = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorUniqueId($this->actorUniqueId);
		$out->putString($this->data);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleDebugInfo($this);
	}
}
