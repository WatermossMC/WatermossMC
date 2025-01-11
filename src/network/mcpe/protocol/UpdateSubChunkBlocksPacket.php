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
use watermossmc\network\mcpe\protocol\types\BlockPosition;
use watermossmc\network\mcpe\protocol\types\UpdateSubChunkBlocksPacketEntry;

use function count;

class UpdateSubChunkBlocksPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_SUB_CHUNK_BLOCKS_PACKET;

	private BlockPosition $baseBlockPosition;

	/** @var UpdateSubChunkBlocksPacketEntry[] */
	private array $layer0Updates;
	/** @var UpdateSubChunkBlocksPacketEntry[] */
	private array $layer1Updates;

	/**
	 * @generate-create-func
	 * @param UpdateSubChunkBlocksPacketEntry[] $layer0Updates
	 * @param UpdateSubChunkBlocksPacketEntry[] $layer1Updates
	 */
	public static function create(BlockPosition $baseBlockPosition, array $layer0Updates, array $layer1Updates) : self
	{
		$result = new self();
		$result->baseBlockPosition = $baseBlockPosition;
		$result->layer0Updates = $layer0Updates;
		$result->layer1Updates = $layer1Updates;
		return $result;
	}

	public function getBaseBlockPosition() : BlockPosition
	{
		return $this->baseBlockPosition;
	}

	/** @return UpdateSubChunkBlocksPacketEntry[] */
	public function getLayer0Updates() : array
	{
		return $this->layer0Updates;
	}

	/** @return UpdateSubChunkBlocksPacketEntry[] */
	public function getLayer1Updates() : array
	{
		return $this->layer1Updates;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->baseBlockPosition = $in->getBlockPosition();
		$this->layer0Updates = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->layer0Updates[] = UpdateSubChunkBlocksPacketEntry::read($in);
		}
		$this->layer1Updates = [];
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$this->layer1Updates[] = UpdateSubChunkBlocksPacketEntry::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBlockPosition($this->baseBlockPosition);
		$out->putUnsignedVarInt(count($this->layer0Updates));
		foreach ($this->layer0Updates as $update) {
			$update->write($out);
		}
		$out->putUnsignedVarInt(count($this->layer1Updates));
		foreach ($this->layer1Updates as $update) {
			$update->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateSubChunkBlocks($this);
	}
}
