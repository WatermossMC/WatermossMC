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

class ActorPickRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ACTOR_PICK_REQUEST_PACKET;

	public int $actorUniqueId;
	public int $hotbarSlot;
	public bool $addUserData;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorUniqueId, int $hotbarSlot, bool $addUserData) : self
	{
		$result = new self();
		$result->actorUniqueId = $actorUniqueId;
		$result->hotbarSlot = $hotbarSlot;
		$result->addUserData = $addUserData;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorUniqueId = $in->getLLong();
		$this->hotbarSlot = $in->getByte();
		$this->addUserData = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLLong($this->actorUniqueId);
		$out->putByte($this->hotbarSlot);
		$out->putBool($this->addUserData);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleActorPickRequest($this);
	}
}
