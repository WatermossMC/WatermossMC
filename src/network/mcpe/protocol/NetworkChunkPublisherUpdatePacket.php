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
use watermossmc\network\mcpe\protocol\types\ChunkPosition;

use function count;

class NetworkChunkPublisherUpdatePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::NETWORK_CHUNK_PUBLISHER_UPDATE_PACKET;

	public BlockPosition $blockPosition;
	public int $radius;
	/** @var ChunkPosition[] */
	public array $savedChunks = [];

	public const MAX_SAVED_CHUNKS = 9216;

	/**
	 * @generate-create-func
	 * @param ChunkPosition[] $savedChunks
	 */
	public static function create(BlockPosition $blockPosition, int $radius, array $savedChunks) : self
	{
		$result = new self();
		$result->blockPosition = $blockPosition;
		$result->radius = $radius;
		$result->savedChunks = $savedChunks;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->blockPosition = $in->getSignedBlockPosition();
		$this->radius = $in->getUnsignedVarInt();

		$count = $in->getLInt();
		if ($count > self::MAX_SAVED_CHUNKS) {
			throw new PacketDecodeException("Expected at most " . self::MAX_SAVED_CHUNKS . " saved chunks, got " . $count);
		}
		for ($i = 0, $this->savedChunks = []; $i < $count; $i++) {
			$this->savedChunks[] = ChunkPosition::read($in);
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putSignedBlockPosition($this->blockPosition);
		$out->putUnsignedVarInt($this->radius);

		$out->putLInt(count($this->savedChunks));
		foreach ($this->savedChunks as $chunk) {
			$chunk->write($out);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleNetworkChunkPublisherUpdate($this);
	}
}
