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
use watermossmc\network\mcpe\protocol\types\SubChunkPacketEntryWithCache as EntryWithBlobHash;
use watermossmc\network\mcpe\protocol\types\SubChunkPacketEntryWithCacheList as ListWithBlobHashes;
use watermossmc\network\mcpe\protocol\types\SubChunkPacketEntryWithoutCache as EntryWithoutBlobHash;
use watermossmc\network\mcpe\protocol\types\SubChunkPacketEntryWithoutCacheList as ListWithoutBlobHashes;
use watermossmc\network\mcpe\protocol\types\SubChunkPosition;

use function count;

class SubChunkPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SUB_CHUNK_PACKET;

	private int $dimension;
	private SubChunkPosition $baseSubChunkPosition;
	private ListWithBlobHashes|ListWithoutBlobHashes $entries;

	/**
	 * @generate-create-func
	 */
	public static function create(int $dimension, SubChunkPosition $baseSubChunkPosition, ListWithBlobHashes|ListWithoutBlobHashes $entries) : self
	{
		$result = new self();
		$result->dimension = $dimension;
		$result->baseSubChunkPosition = $baseSubChunkPosition;
		$result->entries = $entries;
		return $result;
	}

	public function isCacheEnabled() : bool
	{
		return $this->entries instanceof ListWithBlobHashes;
	}

	public function getDimension() : int
	{
		return $this->dimension;
	}

	public function getBaseSubChunkPosition() : SubChunkPosition
	{
		return $this->baseSubChunkPosition;
	}

	public function getEntries() : ListWithBlobHashes|ListWithoutBlobHashes
	{
		return $this->entries;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$cacheEnabled = $in->getBool();
		$this->dimension = $in->getVarInt();
		$this->baseSubChunkPosition = SubChunkPosition::read($in);

		$count = $in->getLInt();
		if ($cacheEnabled) {
			$entries = [];
			for ($i = 0; $i < $count; $i++) {
				$entries[] = EntryWithBlobHash::read($in);
			}
			$this->entries = new ListWithBlobHashes($entries);
		} else {
			$entries = [];
			for ($i = 0; $i < $count; $i++) {
				$entries[] = EntryWithoutBlobHash::read($in);
			}
			$this->entries = new ListWithoutBlobHashes($entries);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBool($this->entries instanceof ListWithBlobHashes);
		$out->putVarInt($this->dimension);
		$this->baseSubChunkPosition->write($out);

		$out->putLInt(count($this->entries->getEntries()));

		foreach ($this->entries->getEntries() as $entry) {
			$entry->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSubChunk($this);
	}
}
