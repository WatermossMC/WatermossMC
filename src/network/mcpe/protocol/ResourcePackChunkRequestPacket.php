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

class ResourcePackChunkRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::RESOURCE_PACK_CHUNK_REQUEST_PACKET;

	public string $packId;
	public int $chunkIndex;

	/**
	 * @generate-create-func
	 */
	public static function create(string $packId, int $chunkIndex) : self
	{
		$result = new self();
		$result->packId = $packId;
		$result->chunkIndex = $chunkIndex;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->packId = $in->getString();
		$this->chunkIndex = $in->getLInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->packId);
		$out->putLInt($this->chunkIndex);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleResourcePackChunkRequest($this);
	}
}
