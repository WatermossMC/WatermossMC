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
use watermossmc\network\mcpe\protocol\types\entity\MetadataProperty;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class AddItemActorPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_ITEM_ACTOR_PACKET;

	public int $actorUniqueId;
	public int $actorRuntimeId;
	public ItemStackWrapper $item;
	public Vector3 $position;
	public ?Vector3 $motion = null;
	/**
	 * @var MetadataProperty[]
	 * @phpstan-var array<int, MetadataProperty>
	 */
	public array $metadata = [];
	public bool $isFromFishing = false;

	/**
	 * @generate-create-func
	 * @param MetadataProperty[] $metadata
	 * @phpstan-param array<int, MetadataProperty> $metadata
	 */
	public static function create(
		int $actorUniqueId,
		int $actorRuntimeId,
		ItemStackWrapper $item,
		Vector3 $position,
		?Vector3 $motion,
		array $metadata,
		bool $isFromFishing,
	) : self {
		$result = new self();
		$result->actorUniqueId = $actorUniqueId;
		$result->actorRuntimeId = $actorRuntimeId;
		$result->item = $item;
		$result->position = $position;
		$result->motion = $motion;
		$result->metadata = $metadata;
		$result->isFromFishing = $isFromFishing;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorUniqueId = $in->getActorUniqueId();
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->item = $in->getItemStackWrapper();
		$this->position = $in->getVector3();
		$this->motion = $in->getVector3();
		$this->metadata = $in->getEntityMetadata();
		$this->isFromFishing = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorUniqueId($this->actorUniqueId);
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putItemStackWrapper($this->item);
		$out->putVector3($this->position);
		$out->putVector3Nullable($this->motion);
		$out->putEntityMetadata($this->metadata);
		$out->putBool($this->isFromFishing);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAddItemActor($this);
	}
}
