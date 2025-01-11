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

class MobArmorEquipmentPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET;

	public int $actorRuntimeId;

	//this intentionally doesn't use an array because we don't want any implicit dependencies on internal order
	public ItemStackWrapper $head;
	public ItemStackWrapper $chest;
	public ItemStackWrapper $legs;
	public ItemStackWrapper $feet;
	public ItemStackWrapper $body;

	/**
	 * @generate-create-func
	 */
	public static function create(int $actorRuntimeId, ItemStackWrapper $head, ItemStackWrapper $chest, ItemStackWrapper $legs, ItemStackWrapper $feet, ItemStackWrapper $body) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->head = $head;
		$result->chest = $chest;
		$result->legs = $legs;
		$result->feet = $feet;
		$result->body = $body;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->actorRuntimeId = $in->getActorRuntimeId();
		$this->head = $in->getItemStackWrapper();
		$this->chest = $in->getItemStackWrapper();
		$this->legs = $in->getItemStackWrapper();
		$this->feet = $in->getItemStackWrapper();
		$this->body = $in->getItemStackWrapper();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putActorRuntimeId($this->actorRuntimeId);
		$out->putItemStackWrapper($this->head);
		$out->putItemStackWrapper($this->chest);
		$out->putItemStackWrapper($this->legs);
		$out->putItemStackWrapper($this->feet);
		$out->putItemStackWrapper($this->body);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleMobArmorEquipment($this);
	}
}
