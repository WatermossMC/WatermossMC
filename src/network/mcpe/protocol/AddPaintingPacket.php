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

class AddPaintingPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_PAINTING_PACKET;

	public int $actorUniqueId;
	public int $actorRuntimeId;
	public Vector3 $position;
	public int $direction;
	public string $title;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorUniqueId, int $actorRuntimeId, Vector3 $position, int $direction, string $title) : self
	{
		$result = new self();
		$result->actorUniqueId = $actorUniqueId;
		$result->actorRuntimeId = $actorRuntimeId;
		$result->position = $position;
		$result->direction = $direction;
		$result->title = $title;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorUniqueId = $in->getActorUniqueId();
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->position = $in->getVector3();
		$this->direction = $in->getVarInt();
		$this->title = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorUniqueId($this->actorUniqueId);
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putVector3($this->position);
		$out->putVarInt($this->direction);
		$out->putString($this->title);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAddPainting($this);
	}
}
