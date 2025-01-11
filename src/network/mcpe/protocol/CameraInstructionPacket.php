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
use watermossmc\network\mcpe\protocol\types\camera\CameraFadeInstruction;
use watermossmc\network\mcpe\protocol\types\camera\CameraSetInstruction;
use watermossmc\network\mcpe\protocol\types\camera\CameraTargetInstruction;

class CameraInstructionPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::CAMERA_INSTRUCTION_PACKET;

	private ?CameraSetInstruction $set;
	private ?bool $clear;
	private ?CameraFadeInstruction $fade;
	private ?CameraTargetInstruction $target;
	private ?bool $removeTarget;

	/**
	 * @generate-create-func
	 */
	public static function create(?CameraSetInstruction $set, ?bool $clear, ?CameraFadeInstruction $fade, ?CameraTargetInstruction $target, ?bool $removeTarget) : self
	{
		$result = new self();
		$result->set = $set;
		$result->clear = $clear;
		$result->fade = $fade;
		$result->target = $target;
		$result->removeTarget = $removeTarget;
		return $result;
	}

	public function getSet() : ?CameraSetInstruction
	{
		return $this->set;
	}

	public function getClear() : ?bool
	{
		return $this->clear;
	}

	public function getFade() : ?CameraFadeInstruction
	{
		return $this->fade;
	}

	public function getTarget() : ?CameraTargetInstruction
	{
		return $this->target;
	}

	public function getRemoveTarget() : ?bool
	{
		return $this->removeTarget;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->set = $in->readOptional(fn () => CameraSetInstruction::read($in));
		$this->clear = $in->readOptional($in->getBool(...));
		$this->fade = $in->readOptional(fn () => CameraFadeInstruction::read($in));
		$this->target = $in->readOptional(fn () => CameraTargetInstruction::read($in));
		$this->removeTarget = $in->readOptional($in->getBool(...));
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->writeOptional($this->set, fn (CameraSetInstruction $v) => $v->write($out));
		$out->writeOptional($this->clear, $out->putBool(...));
		$out->writeOptional($this->fade, fn (CameraFadeInstruction $v) => $v->write($out));
		$out->writeOptional($this->target, fn (CameraTargetInstruction $v) => $v->write($out));
		$out->writeOptional($this->removeTarget, $out->putBool(...));
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleCameraInstruction($this);
	}
}
