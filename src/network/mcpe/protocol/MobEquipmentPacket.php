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
use watermossmc\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class MobEquipmentPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MOB_EQUIPMENT_PACKET;

	public int $actorRuntimeId;
	public ItemStackWrapper $item;
	public int $inventorySlot;
	public int $hotbarSlot;
	public int $windowId = 0;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, ItemStackWrapper $item, int $inventorySlot, int $hotbarSlot, int $windowId) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->item = $item;
		$result->inventorySlot = $inventorySlot;
		$result->hotbarSlot = $hotbarSlot;
		$result->windowId = $windowId;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->item = $in->getItemStackWrapper();
		$this->inventorySlot = $in->getByte();
		$this->hotbarSlot = $in->getByte();
		$this->windowId = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putItemStackWrapper($this->item);
		$out->putByte($this->inventorySlot);
		$out->putByte($this->hotbarSlot);
		$out->putByte($this->windowId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMobEquipment($this);
	}
}
