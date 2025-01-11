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

class BlockPickRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::BLOCK_PICK_REQUEST_PACKET;

	public BlockPosition $blockPosition;
	public bool $addUserData = false;
	public int $hotbarSlot;

	/**
	 * @generate-create-func
	 */
	public static function create(BlockPosition $blockPosition, bool $addUserData, int $hotbarSlot) : self
	{
		$result = new self();
		$result->blockPosition = $blockPosition;
		$result->addUserData = $addUserData;
		$result->hotbarSlot = $hotbarSlot;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->blockPosition = $in->getSignedBlockPosition();
		$this->addUserData = $in->getBool();
		$this->hotbarSlot = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putSignedBlockPosition($this->blockPosition);
		$out->putBool($this->addUserData);
		$out->putByte($this->hotbarSlot);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleBlockPickRequest($this);
	}
}
