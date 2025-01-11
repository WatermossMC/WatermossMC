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

class ResourcePackChunkDataPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_CHUNK_DATA_PACKET;

	public string $packId;
	public int $chunkIndex;
	public int $offset;
	public string $data;

	/**
	 * @generate-create-func
	 */
	public static function create(string $packId, int $chunkIndex, int $offset, string $data) : self
	{
		$result = new self();
		$result->packId = $packId;
		$result->chunkIndex = $chunkIndex;
		$result->offset = $offset;
		$result->data = $data;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->packId = $in->getString();
		$this->chunkIndex = $in->getLInt();
		$this->offset = $in->getLLong();
		$this->data = $in->getString();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->packId);
		$out->putLInt($this->chunkIndex);
		$out->putLLong($this->offset);
		$out->putString($this->data);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleResourcePackChunkData($this);
	}
}
