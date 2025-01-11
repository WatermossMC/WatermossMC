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

class PlayerInputPacket extends DataPacket implements ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::PLAYER_INPUT_PACKET;

	public float $motionX;
	public float $motionY;
	public bool $jumping;
	public bool $sneaking;

	/**
	 * @generate-create-func
	 */
	public static function create(float $motionX, float $motionY, bool $jumping, bool $sneaking) : self
	{
		$result = new self();
		$result->motionX = $motionX;
		$result->motionY = $motionY;
		$result->jumping = $jumping;
		$result->sneaking = $sneaking;
		return $result;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->motionX = $in->getLFloat();
		$this->motionY = $in->getLFloat();
		$this->jumping = $in->getBool();
		$this->sneaking = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLFloat($this->motionX);
		$out->putLFloat($this->motionY);
		$out->putBool($this->jumping);
		$out->putBool($this->sneaking);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handlePlayerInput($this);
	}
}
