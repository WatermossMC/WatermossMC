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

class MobEffectPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MOB_EFFECT_PACKET;

	public const EVENT_ADD = 1;
	public const EVENT_MODIFY = 2;
	public const EVENT_REMOVE = 3;

	public int $actorRuntimeId;
	public int $eventId;
	public int $effectId;
	public int $amplifier = 0;
	public bool $particles = true;
	public int $duration = 0;
	public int $tick = 0;

	/**
	 * @generate-create-func
	 */
	public static function create(
		int $actorRuntimeId,
		int $eventId,
		int $effectId,
		int $amplifier,
		bool $particles,
		int $duration,
		int $tick,
	) : self {
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->eventId = $eventId;
		$result->effectId = $effectId;
		$result->amplifier = $amplifier;
		$result->particles = $particles;
		$result->duration = $duration;
		$result->tick = $tick;
		return $result;
	}

	public static function add(int $actorRuntimeId, bool $replace, int $effectId, int $amplifier, bool $particles, int $duration, int $tick) : self
	{
		return self::create($actorRuntimeId, $replace ? self::EVENT_MODIFY : self::EVENT_ADD, $effectId, $amplifier, $particles, $duration, $tick);
	}

	public static function remove(int $actorRuntimeId, int $effectId, int $tick) : self
	{
		return self::create($actorRuntimeId, self::EVENT_REMOVE, $effectId, 0, false, 0, $tick);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->eventId = $in->getByte();
		$this->effectId = $in->getVarInt();
		$this->amplifier = $in->getVarInt();
		$this->particles = $in->getBool();
		$this->duration = $in->getVarInt();
		$this->tick = $in->getUnsignedVarLong();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putByte($this->eventId);
		$out->putVarInt($this->effectId);
		$out->putVarInt($this->amplifier);
		$out->putBool($this->particles);
		$out->putVarInt($this->duration);
		$out->putUnsignedVarLong($this->tick);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMobEffect($this);
	}
}
