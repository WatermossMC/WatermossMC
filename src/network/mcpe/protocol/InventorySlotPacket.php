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
use watermossmc\network\mcpe\protocol\types\inventory\FullContainerName;
use watermossmc\network\mcpe\protocol\types\inventory\ItemStackWrapper;

class InventorySlotPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::INVENTORY_SLOT_PACKET;

	public int $windowId;
	public int $inventorySlot;
	public FullContainerName $containerName;
	public ItemStackWrapper $storage;
	public ItemStackWrapper $item;

	/**
	 * @generate-create-func
	 */
	public static function create(int $windowId, int $inventorySlot, FullContainerName $containerName, ItemStackWrapper $storage, ItemStackWrapper $item) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->inventorySlot = $inventorySlot;
		$result->containerName = $containerName;
		$result->storage = $storage;
		$result->item = $item;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->windowId = $in->getUnsignedVarInt();
		$this->inventorySlot = $in->getUnsignedVarInt();
		$this->containerName = FullContainerName::read($in);
		$this->storage = $in->getItemStackWrapper();
		$this->item = $in->getItemStackWrapper();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->windowId);
		$out->putUnsignedVarInt($this->inventorySlot);
		$this->containerName->write($out);
		$out->putItemStackWrapper($this->storage);
		$out->putItemStackWrapper($this->item);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleInventorySlot($this);
	}
}
