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

use watermossmc\math\Vector2;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\camera\CameraAimAssistActionType;
use watermossmc\network\mcpe\protocol\types\camera\CameraAimAssistTargetMode;

class CameraAimAssistPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_AIM_ASSIST_PACKET;

	private string $presetId;
	private Vector2 $viewAngle;
	private float $distance;
	private CameraAimAssistTargetMode $targetMode;
	private CameraAimAssistActionType $actionType;

	/**
	 * @generate-create-func
	 */
	public static function create(string $presetId, Vector2 $viewAngle, float $distance, CameraAimAssistTargetMode $targetMode, CameraAimAssistActionType $actionType) : self
	{
		$result = new self();
		$result->presetId = $presetId;
		$result->viewAngle = $viewAngle;
		$result->distance = $distance;
		$result->targetMode = $targetMode;
		$result->actionType = $actionType;
		return $result;
	}

	public function getPresetId() : string
	{
		return $this->presetId;
	}

	public function getViewAngle() : Vector2
	{
		return $this->viewAngle;
	}

	public function getDistance() : float
	{
		return $this->distance;
	}

	public function getTargetMode() : CameraAimAssistTargetMode
	{
		return $this->targetMode;
	}

	public function getActionType() : CameraAimAssistActionType
	{
		return $this->actionType;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->presetId = $in->getString();
		$this->viewAngle = $in->getVector2();
		$this->distance = $in->getLFloat();
		$this->targetMode = CameraAimAssistTargetMode::fromPacket($in->getByte());
		$this->actionType = CameraAimAssistActionType::fromPacket($in->getByte());
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->presetId);
		$out->putVector2($this->viewAngle);
		$out->putLFloat($this->distance);
		$out->putByte($this->targetMode->value);
		$out->putByte($this->actionType->value);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCameraAimAssist($this);
	}
}
