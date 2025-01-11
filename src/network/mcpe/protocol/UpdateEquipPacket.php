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

class UpdateEquipPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::UPDATE_EQUIP_PACKET;

	public int $windowId;
	public int $windowType;
	public int $windowSlotCount; //useless, seems to be part of a standard container header
	public int $actorUniqueId;
	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	public CacheableNbt $nbt;

	/**
	 * @generate-create-func
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $nbt
	 */
	public static function create(int $windowId, int $windowType, int $windowSlotCount, int $actorUniqueId, CacheableNbt $nbt) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->windowType = $windowType;
		$result->windowSlotCount = $windowSlotCount;
		$result->actorUniqueId = $actorUniqueId;
		$result->nbt = $nbt;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->windowId = $in->getByte();
		$this->windowType = $in->getByte();
		$this->windowSlotCount = $in->getVarInt();
		$this->actorUniqueId = $in->getActorUniqueId();
		$this->nbt = new CacheableNbt($in->getNbtCompoundRoot());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->windowId);
		$out->putByte($this->windowType);
		$out->putVarInt($this->windowSlotCount);
		$out->putActorUniqueId($this->actorUniqueId);
		$out->put($this->nbt->getEncodedNbt());
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleUpdateEquip($this);
	}
}
