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
use watermossmc\network\mcpe\protocol\types\ChunkCacheBlob;

use function count;

class ClientCacheMissResponsePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_MISS_RESPONSE_PACKET;

	/** @var ChunkCacheBlob[] */
	private array $blobs = [];

	/**
	 * @generate-create-func
	 * @param ChunkCacheBlob[] $blobs
	 */
	public static function create(array $blobs) : self
	{
		$result = new self();
		$result->blobs = $blobs;
		return $result;
	}

	/**
	 * @return ChunkCacheBlob[]
	 */
	public function getBlobs() : array
	{
		return $this->blobs;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$hash = $in->getLLong();
			$payload = $in->getString();
			$this->blobs[] = new ChunkCacheBlob($hash, $payload);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->blobs));
		foreach ($this->blobs as $blob) {
			$out->putLLong($blob->getHash());
			$out->putString($blob->getPayload());
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleClientCacheMissResponse($this);
	}
}
