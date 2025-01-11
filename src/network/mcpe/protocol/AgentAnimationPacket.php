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

class AgentAnimationPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::AGENT_ANIMATION_PACKET;

	public const TYPE_ARM_SWING = 0;
	public const TYPE_SHRUG = 1;

	private int $animationType;
	private int $actorRuntimeId;

	/**
	 * @generate-create-func
	 */
	public static function create(int $animationType, int $actorRuntimeId) : self
	{
		$result = new self();
		$result->animationType = $animationType;
		$result->actorRuntimeId = $actorRuntimeId;
		return $result;
	}

	public function getAnimationType() : int
	{
		return $this->animationType;
	}

	public function getActorRuntimeId() : int
	{
		return $this->actorRuntimeId;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->animationType = $in->getByte();
		$this->actorRuntimeId = $in->getActorRuntimeId();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->animationType);
		$out->putActorRuntimeId($this->actorRuntimeId);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleAgentAnimation($this);
	}
}
