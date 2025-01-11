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
use watermossmc\network\mcpe\protocol\types\entity\MetadataProperty;
use watermossmc\network\mcpe\protocol\types\entity\PropertySyncData;

class SetActorDataPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{ //TODO: check why this is serverbound
	public const NETWORK_ID = ProtocolInfo::SET_ACTOR_DATA_PACKET;

	public int $actorRuntimeId;
	/**
	 * @var MetadataProperty[]
	 * @phpstan-var array<int, MetadataProperty>
	 */
	public array $metadata;
	public PropertySyncData $syncedProperties;
	public int $tick = 0;

	/**
	 * @generate-create-func
	 * @param MetadataProperty[] $metadata
	 * @phpstan-param array<int, MetadataProperty> $metadata
	 */
	public static function create(int $actorRuntimeId, array $metadata, PropertySyncData $syncedProperties, int $tick) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->metadata = $metadata;
		$result->syncedProperties = $syncedProperties;
		$result->tick = $tick;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->metadata = $in->getEntityMetadata();
		$this->syncedProperties = PropertySyncData::read($in);
		$this->tick = $in->getUnsignedVarLong();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putEntityMetadata($this->metadata);
		$this->syncedProperties->write($out);
		$out->putUnsignedVarLong($this->tick);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetActorData($this);
	}
}
