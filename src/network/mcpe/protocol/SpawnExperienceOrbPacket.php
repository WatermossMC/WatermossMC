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

class SpawnExperienceOrbPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SPAWN_EXPERIENCE_ORB_PACKET;

	public Vector3 $position;
	public int $amount;

	/**
	 * @generate-create-func
	 */
	public static function create(Vector3 $position, int $amount) : self
	{
		$result = new self();
		$result->position = $position;
		$result->amount = $amount;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->position = $in->getVector3();
		$this->amount = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVector3($this->position);
		$out->putVarInt($this->amount);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSpawnExperienceOrb($this);
	}
}
