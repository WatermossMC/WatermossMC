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

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

class RespawnPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::RESPAWN_PACKET;

	public const SEARCHING_FOR_SPAWN = 0;
	public const READY_TO_SPAWN = 1;
	public const CLIENT_READY_TO_SPAWN = 2;

	public Vector3 $position;
	public int $respawnState = self::SEARCHING_FOR_SPAWN;
	public int $actorRuntimeId;

	/**
	 * @generate-create-func
	 */
	public static function create(Vector3 $position, int $respawnState, int $actorRuntimeId) : self
	{
		$result = new self();
		$result->position = $position;
		$result->respawnState = $respawnState;
		$result->actorRuntimeId = $actorRuntimeId;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->position = $in->getVector3();
		$this->respawnState = $in->getByte();
		$this->actorRuntimeId = $in->getActorRuntimeId();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVector3($this->position);
		$out->putByte($this->respawnState);
		$out->putActorRuntimeId($this->actorRuntimeId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleRespawn($this);
	}
}
