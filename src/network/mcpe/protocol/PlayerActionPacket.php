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
use watermossmc\network\mcpe\protocol\types\PlayerAction;

class PlayerActionPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_ACTION_PACKET;

	public int $actorRuntimeId;
	/** @see PlayerAction */
	public int $action;
	public BlockPosition $blockPosition;
	public BlockPosition $resultPosition;
	public int $face;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, int $action, BlockPosition $blockPosition, BlockPosition $resultPosition, int $face) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->action = $action;
		$result->blockPosition = $blockPosition;
		$result->resultPosition = $resultPosition;
		$result->face = $face;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->action = $in->getVarInt();
		$this->blockPosition = $in->getBlockPosition();
		$this->resultPosition = $in->getBlockPosition();
		$this->face = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putVarInt($this->action);
		$out->putBlockPosition($this->blockPosition);
		$out->putBlockPosition($this->resultPosition);
		$out->putVarInt($this->face);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePlayerAction($this);
	}
}
