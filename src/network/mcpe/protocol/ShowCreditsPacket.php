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

class ShowCreditsPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SHOW_CREDITS_PACKET;

	public const STATUS_START_CREDITS = 0;
	public const STATUS_END_CREDITS = 1;

	public int $playerActorRuntimeId;
	public int $status;

	/**
	 * @generate-create-func
	 */
	public static function create(int $playerActorRuntimeId, int $status) : self
	{
		$result = new self();
		$result->playerActorRuntimeId = $playerActorRuntimeId;
		$result->status = $status;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->playerActorRuntimeId = $in->getActorRuntimeId();
		$this->status = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->playerActorRuntimeId);
		$out->putVarInt($this->status);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleShowCredits($this);
	}
}
