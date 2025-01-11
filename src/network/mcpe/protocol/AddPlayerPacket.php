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

use Ramsey\Uuid\UuidInterface;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\DeviceOS;
use watermossmc\network\mcpe\protocol\types\entity\EntityLink;
use watermossmc\network\mcpe\protocol\types\entity\MetadataProperty;
use watermossmc\network\mcpe\protocol\types\entity\PropertySyncData;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStackWrapper;

use function count;

class AddPlayerPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ADD_PLAYER_PACKET;

	public UuidInterface $uuid;
	public string $username;
	public int $actorRuntimeId;
	public string $platformChatId = "";
	public Vector3 $position;
	public ?Vector3 $motion = null;
	public float $pitch = 0.0;
	public float $yaw = 0.0;
	public float $headYaw = 0.0;
	public ItemStackWrapper $item;
	public int $gameMode;
	/**
	 * @var MetadataProperty[]
	 * @phpstan-var array<int, MetadataProperty>
	 */
	public array $metadata = [];
	public PropertySyncData $syncedProperties;

	public UpdateAbilitiesPacket $abilitiesPacket;

	/** @var EntityLink[] */
	public array $links = [];
	public string $deviceId = ""; //TODO: fill player's device ID (???)
	public int $buildPlatform = DeviceOS::UNKNOWN;

	/**
	 * @generate-create-func
	 * @param MetadataProperty[] $metadata
	 * @param EntityLink[]       $links
	 * @phpstan-param array<int, MetadataProperty> $metadata
	 */
	public static function create(
		UuidInterface $uuid,
		string $username,
		int $actorRuntimeId,
		string $platformChatId,
		Vector3 $position,
		?Vector3 $motion,
		float $pitch,
		float $yaw,
		float $headYaw,
		ItemStackWrapper $item,
		int $gameMode,
		array $metadata,
		PropertySyncData $syncedProperties,
		UpdateAbilitiesPacket $abilitiesPacket,
		array $links,
		string $deviceId,
		int $buildPlatform,
	) : self {
		$result = new self();
		$result->uuid = $uuid;
		$result->username = $username;
		$result->actorRuntimeId = $actorRuntimeId;
		$result->platformChatId = $platformChatId;
		$result->position = $position;
		$result->motion = $motion;
		$result->pitch = $pitch;
		$result->yaw = $yaw;
		$result->headYaw = $headYaw;
		$result->item = $item;
		$result->gameMode = $gameMode;
		$result->metadata = $metadata;
		$result->syncedProperties = $syncedProperties;
		$result->abilitiesPacket = $abilitiesPacket;
		$result->links = $links;
		$result->deviceId = $deviceId;
		$result->buildPlatform = $buildPlatform;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->uuid = $in->getUUID();
		$this->username = $in->getString();
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->platformChatId = $in->getString();
		$this->position = $in->getVector3();
		$this->motion = $in->getVector3();
		$this->pitch = $in->getLFloat();
		$this->yaw = $in->getLFloat();
		$this->headYaw = $in->getLFloat();
		$this->item = $in->getItemStackWrapper();
		$this->gameMode = $in->getVarInt();
		$this->metadata = $in->getEntityMetadata();
		$this->syncedProperties = PropertySyncData::read($in);

		$this->abilitiesPacket = new UpdateAbilitiesPacket();
		$this->abilitiesPacket->decodePayload($in);

		$linkCount = $in->getUnsignedVarInt();
		for ($i = 0; $i < $linkCount; ++$i) {
			$this->links[$i] = $in->getEntityLink();
		}

		$this->deviceId = $in->getString();
		$this->buildPlatform = $in->getLInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUUID($this->uuid);
		$out->putString($this->username);
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putString($this->platformChatId);
		$out->putVector3($this->position);
		$out->putVector3Nullable($this->motion);
		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);
		$out->putLFloat($this->headYaw);
		$out->putItemStackWrapper($this->item);
		$out->putVarInt($this->gameMode);
		$out->putEntityMetadata($this->metadata);
		$this->syncedProperties->write($out);

		$this->abilitiesPacket->encodePayload($out);

		$out->putUnsignedVarInt(count($this->links));
		foreach ($this->links as $link) {
			$out->putEntityLink($link);
		}

		$out->putString($this->deviceId);
		$out->putLInt($this->buildPlatform);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAddPlayer($this);
	}
}
