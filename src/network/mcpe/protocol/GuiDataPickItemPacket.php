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

class GuiDataPickItemPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::GUI_DATA_PICK_ITEM_PACKET;

	public string $itemDescription;
	public string $itemEffects;
	public int $hotbarSlot;

	/**
	 * @generate-create-func
	 */
	public static function create(string $itemDescription, string $itemEffects, int $hotbarSlot) : self
	{
		$result = new self();
		$result->itemDescription = $itemDescription;
		$result->itemEffects = $itemEffects;
		$result->hotbarSlot = $hotbarSlot;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->itemDescription = $in->getString();
		$this->itemEffects = $in->getString();
		$this->hotbarSlot = $in->getLInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->itemDescription);
		$out->putString($this->itemEffects);
		$out->putLInt($this->hotbarSlot);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleGuiDataPickItem($this);
	}
}
