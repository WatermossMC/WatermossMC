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

class PositionTrackingDBClientRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::POSITION_TRACKING_D_B_CLIENT_REQUEST_PACKET;

	public const ACTION_QUERY = 0;

	private int $action;
	private int $trackingId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $action, int $trackingId) : self
	{
		$result = new self();
		$result->action = $action;
		$result->trackingId = $trackingId;
		return $result;
	}

	public function getAction() : int
	{
		return $this->action;
	}

	public function getTrackingId() : int
	{
		return $this->trackingId;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->action = $in->getByte();
		$this->trackingId = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->action);
		$out->putVarInt($this->trackingId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePositionTrackingDBClientRequest($this);
	}
}
