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
use watermossmc\network\mcpe\protocol\types\inventory\ContainerIds;

class PlayerHotbarPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_HOTBAR_PACKET;

	public int $selectedHotbarSlot;
	public int $windowId = ContainerIds::INVENTORY;
	public bool $selectHotbarSlot = true;

	/**
	 * @generate-create-func
	 */
	public static function create(int $selectedHotbarSlot, int $windowId, bool $selectHotbarSlot) : self
	{
		$result = new self();
		$result->selectedHotbarSlot = $selectedHotbarSlot;
		$result->windowId = $windowId;
		$result->selectHotbarSlot = $selectHotbarSlot;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->selectedHotbarSlot = $in->getUnsignedVarInt();
		$this->windowId = $in->getByte();
		$this->selectHotbarSlot = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt($this->selectedHotbarSlot);
		$out->putByte($this->windowId);
		$out->putBool($this->selectHotbarSlot);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePlayerHotbar($this);
	}
}
