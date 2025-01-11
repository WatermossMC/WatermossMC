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
use watermossmc\network\mcpe\protocol\types\DimensionIds;

class SpawnParticleEffectPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SPAWN_PARTICLE_EFFECT_PACKET;

	public int $dimensionId = DimensionIds::OVERWORLD; //wtf mojang
	public int $actorUniqueId = -1; //default none
	public Vector3 $position;
	public string $particleName;
	public ?string $molangVariablesJson = null;

	/**
	 * @generate-create-func
	 */
	public static function create(int $dimensionId, int $actorUniqueId, Vector3 $position, string $particleName, ?string $molangVariablesJson) : self
	{
		$result = new self();
		$result->dimensionId = $dimensionId;
		$result->actorUniqueId = $actorUniqueId;
		$result->position = $position;
		$result->particleName = $particleName;
		$result->molangVariablesJson = $molangVariablesJson;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->dimensionId = $in->getByte();
		$this->actorUniqueId = $in->getActorUniqueId();
		$this->position = $in->getVector3();
		$this->particleName = $in->getString();
		$this->molangVariablesJson = $in->getBool() ? $in->getString() : null;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->dimensionId);
		$out->putActorUniqueId($this->actorUniqueId);
		$out->putVector3($this->position);
		$out->putString($this->particleName);
		$out->putBool($this->molangVariablesJson !== null);
		if ($this->molangVariablesJson !== null) {
			$out->putString($this->molangVariablesJson);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSpawnParticleEffect($this);
	}
}
