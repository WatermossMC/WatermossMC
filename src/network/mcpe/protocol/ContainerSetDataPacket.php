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

class ContainerSetDataPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CONTAINER_SET_DATA_PACKET;

	public const PROPERTY_FURNACE_SMELT_PROGRESS = 0;
	public const PROPERTY_FURNACE_REMAINING_FUEL_TIME = 1;
	public const PROPERTY_FURNACE_MAX_FUEL_TIME = 2;
	public const PROPERTY_FURNACE_STORED_XP = 3;
	public const PROPERTY_FURNACE_FUEL_AUX = 4;

	public const PROPERTY_BREWING_STAND_BREW_TIME = 0;
	public const PROPERTY_BREWING_STAND_FUEL_AMOUNT = 1;
	public const PROPERTY_BREWING_STAND_FUEL_TOTAL = 2;

	public int $windowId;
	public int $property;
	public int $value;

	/**
	 * @generate-create-func
	 */
	public static function create(int $windowId, int $property, int $value) : self
	{
		$result = new self();
		$result->windowId = $windowId;
		$result->property = $property;
		$result->value = $value;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->windowId = $in->getByte();
		$this->property = $in->getVarInt();
		$this->value = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->windowId);
		$out->putVarInt($this->property);
		$out->putVarInt($this->value);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleContainerSetData($this);
	}
}
