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

class LabTablePacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::LAB_TABLE_PACKET;

	public const TYPE_START_COMBINE = 0;
	public const TYPE_START_REACTION = 1;
	public const TYPE_RESET = 2;

	public int $actionType;
	public BlockPosition $blockPosition;
	public int $reactionType;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actionType, BlockPosition $blockPosition, int $reactionType) : self
	{
		$result = new self();
		$result->actionType = $actionType;
		$result->blockPosition = $blockPosition;
		$result->reactionType = $reactionType;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actionType = $in->getByte();
		$this->blockPosition = $in->getSignedBlockPosition();
		$this->reactionType = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->actionType);
		$out->putSignedBlockPosition($this->blockPosition);
		$out->putByte($this->reactionType);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleLabTable($this);
	}
}
