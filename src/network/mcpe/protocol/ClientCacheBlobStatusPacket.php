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

use function count;

class ClientCacheBlobStatusPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_BLOB_STATUS_PACKET;

	/** @var int[] xxHash64 subchunk data hashes */
	private array $hitHashes = [];
	/** @var int[] xxHash64 subchunk data hashes */
	private array $missHashes = [];

	/**
	 * @generate-create-func
	 * @param int[] $hitHashes
	 * @param int[] $missHashes
	 */
	public static function create(array $hitHashes, array $missHashes) : self
	{
		$result = new self();
		$result->hitHashes = $hitHashes;
		$result->missHashes = $missHashes;
		return $result;
	}

	/**
	 * @return int[]
	 */
	public function getHitHashes() : array
	{
		return $this->hitHashes;
	}

	/**
	 * @return int[]
	 */
	public function getMissHashes() : array
	{
		return $this->missHashes;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$missCount = $in->getUnsignedVarInt();
		$hitCount = $in->getUnsignedVarInt();
		for ($i = 0; $i < $missCount; ++$i) {
			$this->missHashes[] = $in->getLLong();
		}
		for ($i = 0; $i < $hitCount; ++$i) {
			$this->hitHashes[] = $in->getLLong();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->missHashes));
		$out->putUnsignedVarInt(count($this->hitHashes));
		foreach ($this->missHashes as $hash) {
			$out->putLLong($hash);
		}
		foreach ($this->hitHashes as $hash) {
			$out->putLLong($hash);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleClientCacheBlobStatus($this);
	}
}
