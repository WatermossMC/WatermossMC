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
use watermossmc\network\mcpe\protocol\types\BlockPosition;

class PlayerToggleCrafterSlotRequestPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_TOGGLE_CRAFTER_SLOT_REQUEST_PACKET;

	private BlockPosition $position;
	private int $slot;
	private bool $disabled;

	/**
	 * @generate-create-func
	 */
	public static function create(BlockPosition $position, int $slot, bool $disabled) : self
	{
		$result = new self();
		$result->position = $position;
		$result->slot = $slot;
		$result->disabled = $disabled;
		return $result;
	}

	public function getPosition() : BlockPosition
	{
		return $this->position;
	}

	public function getSlot() : int
	{
		return $this->slot;
	}

	public function isDisabled() : bool
	{
		return $this->disabled;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$x = $in->getLInt();
		$y = $in->getLInt();
		$z = $in->getLInt();
		$this->position = new BlockPosition($x, $y, $z);
		$this->slot = $in->getByte();
		$this->disabled = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLInt($this->position->getX());
		$out->putLInt($this->position->getY());
		$out->putLInt($this->position->getZ());
		$out->putByte($this->slot);
		$out->putBool($this->disabled);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePlayerToggleCrafterSlotRequest($this);
	}
}
