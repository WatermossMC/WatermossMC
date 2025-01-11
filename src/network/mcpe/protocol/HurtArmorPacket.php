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

class HurtArmorPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::HURT_ARMOR_PACKET;

	public int $cause;
	public int $health;
	public int $armorSlotFlags;

	/**
	 * @generate-create-func
	 */
	public static function create(int $cause, int $health, int $armorSlotFlags) : self
	{
		$result = new self();
		$result->cause = $cause;
		$result->health = $health;
		$result->armorSlotFlags = $armorSlotFlags;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->cause = $in->getVarInt();
		$this->health = $in->getVarInt();
		$this->armorSlotFlags = $in->getUnsignedVarLong();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->cause);
		$out->putVarInt($this->health);
		$out->putUnsignedVarLong($this->armorSlotFlags);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleHurtArmor($this);
	}
}
