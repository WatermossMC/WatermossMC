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

class InteractPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::INTERACT_PACKET;

	public const ACTION_LEAVE_VEHICLE = 3;
	public const ACTION_MOUSEOVER = 4;
	public const ACTION_OPEN_NPC = 5;
	public const ACTION_OPEN_INVENTORY = 6;

	public int $action;
	public int $targetActorRuntimeId;
	public float $x;
	public float $y;
	public float $z;

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->action = $in->getByte();
		$this->targetActorRuntimeId = $in->getActorRuntimeId();

		if ($this->action === self::ACTION_MOUSEOVER || $this->action === self::ACTION_LEAVE_VEHICLE) {
			//TODO: should this be a vector3?
			$this->x = $in->getLFloat();
			$this->y = $in->getLFloat();
			$this->z = $in->getLFloat();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->action);
		$out->putActorRuntimeId($this->targetActorRuntimeId);

		if ($this->action === self::ACTION_MOUSEOVER || $this->action === self::ACTION_LEAVE_VEHICLE) {
			$out->putLFloat($this->x);
			$out->putLFloat($this->y);
			$out->putLFloat($this->z);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleInteract($this);
	}
}
