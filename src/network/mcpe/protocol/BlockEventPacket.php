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

class BlockEventPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::BLOCK_EVENT_PACKET;

	public BlockPosition $blockPosition;
	public int $eventType;
	public int $eventData;

	/**
	 * @generate-create-func
	 */
	public static function create(BlockPosition $blockPosition, int $eventType, int $eventData) : self
	{
		$result = new self();
		$result->blockPosition = $blockPosition;
		$result->eventType = $eventType;
		$result->eventData = $eventData;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->blockPosition = $in->getBlockPosition();
		$this->eventType = $in->getVarInt();
		$this->eventData = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBlockPosition($this->blockPosition);
		$out->putVarInt($this->eventType);
		$out->putVarInt($this->eventData);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleBlockEvent($this);
	}
}
