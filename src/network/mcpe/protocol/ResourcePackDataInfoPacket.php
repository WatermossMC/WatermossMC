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
use watermossmc\network\mcpe\protocol\types\resourcepacks\ResourcePackType;

class ResourcePackDataInfoPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_DATA_INFO_PACKET;

	public string $packId;
	public int $maxChunkSize;
	public int $chunkCount;
	public int $compressedPackSize;
	public string $sha256;
	public bool $isPremium = false;
	public int $packType = ResourcePackType::RESOURCES; //TODO: check the values for this

	/**
	 * @generate-create-func
	 */
	public static function create(
		string $packId,
		int $maxChunkSize,
		int $chunkCount,
		int $compressedPackSize,
		string $sha256,
		bool $isPremium,
		int $packType,
	) : self {
		$result = new self();
		$result->packId = $packId;
		$result->maxChunkSize = $maxChunkSize;
		$result->chunkCount = $chunkCount;
		$result->compressedPackSize = $compressedPackSize;
		$result->sha256 = $sha256;
		$result->isPremium = $isPremium;
		$result->packType = $packType;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->packId = $in->getString();
		$this->maxChunkSize = $in->getLInt();
		$this->chunkCount = $in->getLInt();
		$this->compressedPackSize = $in->getLLong();
		$this->sha256 = $in->getString();
		$this->isPremium = $in->getBool();
		$this->packType = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->packId);
		$out->putLInt($this->maxChunkSize);
		$out->putLInt($this->chunkCount);
		$out->putLLong($this->compressedPackSize);
		$out->putString($this->sha256);
		$out->putBool($this->isPremium);
		$out->putByte($this->packType);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleResourcePackDataInfo($this);
	}
}
