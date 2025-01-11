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
use watermossmc\network\mcpe\protocol\types\inventory\InventoryLayout;
use watermossmc\network\mcpe\protocol\types\inventory\InventoryLeftTab;
use watermossmc\network\mcpe\protocol\types\inventory\InventoryRightTab;

class SetPlayerInventoryOptionsPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SET_PLAYER_INVENTORY_OPTIONS_PACKET;

	private InventoryLeftTab $leftTab;
	private InventoryRightTab $rightTab;
	private bool $filtering;
	private InventoryLayout $inventoryLayout;
	private InventoryLayout $craftingLayout;

	/**
	 * @generate-create-func
	 */
	public static function create(InventoryLeftTab $leftTab, InventoryRightTab $rightTab, bool $filtering, InventoryLayout $inventoryLayout, InventoryLayout $craftingLayout) : self
	{
		$result = new self();
		$result->leftTab = $leftTab;
		$result->rightTab = $rightTab;
		$result->filtering = $filtering;
		$result->inventoryLayout = $inventoryLayout;
		$result->craftingLayout = $craftingLayout;
		return $result;
	}

	public function getLeftTab() : InventoryLeftTab
	{
		return $this->leftTab;
	}

	public function getRightTab() : InventoryRightTab
	{
		return $this->rightTab;
	}

	public function isFiltering() : bool
	{
		return $this->filtering;
	}

	public function getInventoryLayout() : InventoryLayout
	{
		return $this->inventoryLayout;
	}

	public function getCraftingLayout() : InventoryLayout
	{
		return $this->craftingLayout;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->leftTab = InventoryLeftTab::fromPacket($in->getVarInt());
		$this->rightTab = InventoryRightTab::fromPacket($in->getVarInt());
		$this->filtering = $in->getBool();
		$this->inventoryLayout = InventoryLayout::fromPacket($in->getVarInt());
		$this->craftingLayout = InventoryLayout::fromPacket($in->getVarInt());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->leftTab->value);
		$out->putVarInt($this->rightTab->value);
		$out->putBool($this->filtering);
		$out->putVarInt($this->inventoryLayout->value);
		$out->putVarInt($this->craftingLayout->value);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleSetPlayerInventoryOptions($this);
	}
}
