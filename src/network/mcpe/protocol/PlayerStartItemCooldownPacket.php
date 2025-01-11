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

class PlayerStartItemCooldownPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_START_ITEM_COOLDOWN_PACKET;

	private string $itemCategory;
	private int $cooldownTicks;

	/**
	 * @generate-create-func
	 */
	public static function create(string $itemCategory, int $cooldownTicks) : self
	{
		$result = new self();
		$result->itemCategory = $itemCategory;
		$result->cooldownTicks = $cooldownTicks;
		return $result;
	}

	public function getItemCategory() : string
	{
		return $this->itemCategory;
	}

	public function getCooldownTicks() : int
	{
		return $this->cooldownTicks;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->itemCategory = $in->getString();
		$this->cooldownTicks = $in->getVarInt();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->itemCategory);
		$out->putVarInt($this->cooldownTicks);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePlayerStartItemCooldown($this);
	}
}
