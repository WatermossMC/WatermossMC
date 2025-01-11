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
use watermossmc\network\mcpe\protocol\types\SubChunkPosition;
use watermossmc\network\mcpe\protocol\types\SubChunkPositionOffset;

use function count;

class SubChunkRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SUB_CHUNK_REQUEST_PACKET;

	private int $dimension;
	private SubChunkPosition $basePosition;
	/**
	 * @var SubChunkPositionOffset[]
	 * @phpstan-var list<SubChunkPositionOffset>
	 */
	private array $entries;

	/**
	 * @generate-create-func
	 * @param SubChunkPositionOffset[] $entries
	 * @phpstan-param list<SubChunkPositionOffset> $entries
	 */
	public static function create(int $dimension, SubChunkPosition $basePosition, array $entries) : self
	{
		$result = new self();
		$result->dimension = $dimension;
		$result->basePosition = $basePosition;
		$result->entries = $entries;
		return $result;
	}

	public function getDimension() : int
	{
		return $this->dimension;
	}

	public function getBasePosition() : SubChunkPosition
	{
		return $this->basePosition;
	}

	/**
	 * @return SubChunkPositionOffset[]
	 * @phpstan-return list<SubChunkPositionOffset>
	 */
	public function getEntries() : array
	{
		return $this->entries;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->dimension = $in->getVarInt();
		$this->basePosition = SubChunkPosition::read($in);

		$this->entries = [];
		for ($i = 0, $count = $in->getLInt(); $i < $count; $i++) {
			$this->entries[] = SubChunkPositionOffset::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->dimension);
		$this->basePosition->write($out);

		$out->putLInt(count($this->entries));
		foreach ($this->entries as $entry) {
			$entry->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSubChunkRequest($this);
	}
}
