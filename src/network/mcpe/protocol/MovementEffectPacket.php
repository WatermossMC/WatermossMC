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
use watermossmc\network\mcpe\protocol\types\MovementEffectType;

class MovementEffectPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MOVEMENT_EFFECT_PACKET;

	private int $actorRuntimeId;
	private MovementEffectType $effectType;
	private int $duration;
	private int $tick;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, MovementEffectType $effectType, int $duration, int $tick) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->effectType = $effectType;
		$result->duration = $duration;
		$result->tick = $tick;
		return $result;
	}

	public function getActorRuntimeId() : int
	{
		return $this->actorRuntimeId;
	}

	public function getEffectType() : MovementEffectType
	{
		return $this->effectType;
	}

	public function getDuration() : int
	{
		return $this->duration;
	}

	public function getTick() : int
	{
		return $this->tick;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->effectType = MovementEffectType::fromPacket($in->getUnsignedVarInt());
		$this->duration = $in->getUnsignedVarInt();
		$this->tick = $in->getUnsignedVarLong();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putUnsignedVarInt($this->effectType->value);
		$out->putUnsignedVarInt($this->duration);
		$out->putUnsignedVarLong($this->tick);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMovementEffect($this);
	}
}
