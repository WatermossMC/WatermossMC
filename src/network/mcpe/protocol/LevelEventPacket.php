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
use watermossmc\network\mcpe\protocol\types\LevelEvent;

class LevelEventPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::LEVEL_EVENT_PACKET;

	/** @see LevelEvent */
	public int $eventId;
	public int $eventData;
	public ?Vector3 $position = null;

	/**
	 * @generate-create-func
	 */
	public static function create(int $eventId, int $eventData, ?Vector3 $position) : self
	{
		$result = new self();
		$result->eventId = $eventId;
		$result->eventData = $eventData;
		$result->position = $position;
		return $result;
	}

	public static function standardParticle(int $particleId, int $data, Vector3 $position) : self
	{
		return self::create(LevelEvent::ADD_PARTICLE_MASK | $particleId, $data, $position);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->eventId = $in->getVarInt();
		$this->position = $in->getVector3();
		$this->eventData = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->eventId);
		$out->putVector3Nullable($this->position);
		$out->putVarInt($this->eventData);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleLevelEvent($this);
	}
}
