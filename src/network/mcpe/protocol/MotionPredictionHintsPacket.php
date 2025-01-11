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

class MotionPredictionHintsPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MOTION_PREDICTION_HINTS_PACKET;

	private int $actorRuntimeId;
	private Vector3 $motion;
	private bool $onGround;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, Vector3 $motion, bool $onGround) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->motion = $motion;
		$result->onGround = $onGround;
		return $result;
	}

	public function getActorRuntimeId() : int
	{
		return $this->actorRuntimeId;
	}

	public function getMotion() : Vector3
	{
		return $this->motion;
	}

	public function isOnGround() : bool
	{
		return $this->onGround;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->motion = $in->getVector3();
		$this->onGround = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putVector3($this->motion);
		$out->putBool($this->onGround);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMotionPredictionHints($this);
	}
}
