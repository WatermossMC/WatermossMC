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

class CameraShakePacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_SHAKE_PACKET;

	public const TYPE_POSITIONAL = 0;
	public const TYPE_ROTATIONAL = 1;

	public const ACTION_ADD = 0;
	public const ACTION_STOP = 1;

	private float $intensity;
	private float $duration;
	private int $shakeType;
	private int $shakeAction;

	/**
	 * @generate-create-func
	 */
	public static function create(float $intensity, float $duration, int $shakeType, int $shakeAction) : self
	{
		$result = new self();
		$result->intensity = $intensity;
		$result->duration = $duration;
		$result->shakeType = $shakeType;
		$result->shakeAction = $shakeAction;
		return $result;
	}

	public function getIntensity() : float
	{
		return $this->intensity;
	}

	public function getDuration() : float
	{
		return $this->duration;
	}

	public function getShakeType() : int
	{
		return $this->shakeType;
	}

	public function getShakeAction() : int
	{
		return $this->shakeAction;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->intensity = $in->getLFloat();
		$this->duration = $in->getLFloat();
		$this->shakeType = $in->getByte();
		$this->shakeAction = $in->getByte();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLFloat($this->intensity);
		$out->putLFloat($this->duration);
		$out->putByte($this->shakeType);
		$out->putByte($this->shakeAction);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCameraShake($this);
	}
}
