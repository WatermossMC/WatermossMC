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

class UpdateBlockPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_BLOCK_PACKET;

	public const FLAG_NONE = 0b0000;
	public const FLAG_NEIGHBORS = 0b0001;
	public const FLAG_NETWORK = 0b0010;
	public const FLAG_NOGRAPHIC = 0b0100;
	public const FLAG_PRIORITY = 0b1000;

	public const DATA_LAYER_NORMAL = 0;
	public const DATA_LAYER_LIQUID = 1;

	public BlockPosition $blockPosition;
	public int $blockRuntimeId;
	/**
	 * @var int
	 * Flags are used by MCPE internally for block setting, but only flag 2 (network flag) is relevant for network.
	 * This field is pointless really.
	 */
	public int $flags = self::FLAG_NETWORK;
	public int $dataLayerId = self::DATA_LAYER_NORMAL;

	/**
	 * @generate-create-func
	 */
	public static function create(BlockPosition $blockPosition, int $blockRuntimeId, int $flags, int $dataLayerId) : self
	{
		$result = new self();
		$result->blockPosition = $blockPosition;
		$result->blockRuntimeId = $blockRuntimeId;
		$result->flags = $flags;
		$result->dataLayerId = $dataLayerId;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->blockPosition = $in->getBlockPosition();
		$this->blockRuntimeId = $in->getUnsignedVarInt();
		$this->flags = $in->getUnsignedVarInt();
		$this->dataLayerId = $in->getUnsignedVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putBlockPosition($this->blockPosition);
		$out->putUnsignedVarInt($this->blockRuntimeId);
		$out->putUnsignedVarInt($this->flags);
		$out->putUnsignedVarInt($this->dataLayerId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateBlock($this);
	}
}
