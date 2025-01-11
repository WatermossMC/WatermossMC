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
use watermossmc\network\mcpe\protocol\types\CacheableNbt;

class PositionTrackingDBServerBroadcastPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::POSITION_TRACKING_D_B_SERVER_BROADCAST_PACKET;

	public const ACTION_UPDATE = 0;
	public const ACTION_DESTROY = 1;
	public const ACTION_NOT_FOUND = 2;

	private int $action;
	private int $trackingId;
	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	private CacheableNbt $nbt;

	/**
	 * @generate-create-func
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $nbt
	 */
	public static function create(int $action, int $trackingId, CacheableNbt $nbt) : self
	{
		$result = new self();
		$result->action = $action;
		$result->trackingId = $trackingId;
		$result->nbt = $nbt;
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

	/** @phpstan-return CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	public function getNbt() : CacheableNbt
	{
		return $this->nbt;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->action = $in->getByte();
		$this->trackingId = $in->getVarInt();
		$this->nbt = new CacheableNbt($in->getNbtCompoundRoot());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->action);
		$out->putVarInt($this->trackingId);
		$out->put($this->nbt->getEncodedNbt());
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePositionTrackingDBServerBroadcast($this);
	}
}
