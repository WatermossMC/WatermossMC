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

class AnimatePacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::ANIMATE_PACKET;

	public const ACTION_SWING_ARM = 1;

	public const ACTION_STOP_SLEEP = 3;
	public const ACTION_CRITICAL_HIT = 4;
	public const ACTION_MAGICAL_CRITICAL_HIT = 5;
	public const ACTION_ROW_RIGHT = 128;
	public const ACTION_ROW_LEFT = 129;

	public int $action;
	public int $actorRuntimeId;
	public float $float = 0.0; //TODO (Boat rowing time?)

	public static function create(int $actorRuntimeId, int $actionId) : self
	{
		$result = new self();
		$result->actorRuntimeId = $actorRuntimeId;
		$result->action = $actionId;
		return $result;
	}

	public static function boatHack(int $actorRuntimeId, int $actionId, float $data) : self
	{
		$result = self::create($actorRuntimeId, $actionId);
		$result->float = $data;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->action = $in->getVarInt();
		$this->actorRuntimeId = $in->getActorRuntimeId();
		if (($this->action & 0x80) !== 0) {
			$this->float = $in->getLFloat();
		}
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putVarInt($this->action);
		$out->putActorRuntimeId($this->actorRuntimeId);
		if (($this->action & 0x80) !== 0) {
			$out->putLFloat($this->float);
		}
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAnimate($this);
	}
}
