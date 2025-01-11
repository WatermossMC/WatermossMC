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

class TakeItemActorPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::TAKE_ITEM_ACTOR_PACKET;

	public int $takerActorRuntimeId;
	public int $itemActorRuntimeId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $takerActorRuntimeId, int $itemActorRuntimeId) : self
	{
		$result = new self();
		$result->takerActorRuntimeId = $takerActorRuntimeId;
		$result->itemActorRuntimeId = $itemActorRuntimeId;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->itemActorRuntimeId = $in->getActorRuntimeId();
		$this->takerActorRuntimeId = $in->getActorRuntimeId();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->itemActorRuntimeId);
		$out->putActorRuntimeId($this->takerActorRuntimeId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleTakeItemActor($this);
	}
}
