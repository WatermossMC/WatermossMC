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

class MoveActorAbsolutePacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MOVE_ACTOR_ABSOLUTE_PACKET;

	public const FLAG_GROUND = 0x01;
	public const FLAG_TELEPORT = 0x02;
	public const FLAG_FORCE_MOVE_LOCAL_ENTITY = 0x04;

	public int $actorRuntimeId;
	public Vector3 $position;
	public float $pitch;
	public float $yaw;
	public float $headYaw; //always zero for non-mobs
	public int $flags = 0;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, Vector3 $position, float $pitch, float $yaw, float $headYaw, int $flags) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->position = $position;
		$result->pitch = $pitch;
		$result->yaw = $yaw;
		$result->headYaw = $headYaw;
		$result->flags = $flags;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->flags = $in->getByte();
		$this->position = $in->getVector3();
		$this->pitch = $in->getRotationByte();
		$this->yaw = $in->getRotationByte();
		$this->headYaw = $in->getRotationByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putByte($this->flags);
		$out->putVector3($this->position);
		$out->putRotationByte($this->pitch);
		$out->putRotationByte($this->yaw);
		$out->putRotationByte($this->headYaw);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMoveActorAbsolute($this);
	}
}
