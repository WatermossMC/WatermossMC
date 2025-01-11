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

namespace watermossmc\network\mcpe\protocol\types\camera;

use watermossmc\math\Vector2;
use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class CameraSetInstruction
{
	public function __construct(
		private int $preset,
		private ?CameraSetInstructionEase $ease,
		private ?Vector3 $cameraPosition,
		private ?CameraSetInstructionRotation $rotation,
		private ?Vector3 $facingPosition,
		private ?Vector2 $viewOffset,
		private ?Vector3 $entityOffset,
		private ?bool $default
	) {
	}

	public function getPreset() : int
	{
		return $this->preset;
	}

	public function getEase() : ?CameraSetInstructionEase
	{
		return $this->ease;
	}

	public function getCameraPosition() : ?Vector3
	{
		return $this->cameraPosition;
	}

	public function getRotation() : ?CameraSetInstructionRotation
	{
		return $this->rotation;
	}

	public function getFacingPosition() : ?Vector3
	{
		return $this->facingPosition;
	}

	public function getViewOffset() : ?Vector2
	{
		return $this->viewOffset;
	}

	public function getEntityOffset() : ?Vector3
	{
		return $this->entityOffset;
	}

	public function getDefault() : ?bool
	{
		return $this->default;
	}

	public static function read(PacketSerializer $in) : self
	{
		$preset = $in->getLInt();
		$ease = $in->readOptional(fn () => CameraSetInstructionEase::read($in));
		$cameraPosition = $in->readOptional($in->getVector3(...));
		$rotation = $in->readOptional(fn () => CameraSetInstructionRotation::read($in));
		$facingPosition = $in->readOptional($in->getVector3(...));
		$viewOffset = $in->readOptional($in->getVector2(...));
		$entityOffset = $in->readOptional($in->getVector3(...));
		$default = $in->readOptional($in->getBool(...));

		return new self(
			$preset,
			$ease,
			$cameraPosition,
			$rotation,
			$facingPosition,
			$viewOffset,
			$entityOffset,
			$default
		);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLInt($this->preset);
		$out->writeOptional($this->ease, fn (CameraSetInstructionEase $v) => $v->write($out));
		$out->writeOptional($this->cameraPosition, $out->putVector3(...));
		$out->writeOptional($this->rotation, fn (CameraSetInstructionRotation $v) => $v->write($out));
		$out->writeOptional($this->facingPosition, $out->putVector3(...));
		$out->writeOptional($this->viewOffset, $out->putVector2(...));
		$out->writeOptional($this->entityOffset, $out->putVector3(...));
		$out->writeOptional($this->default, $out->putBool(...));
	}
}
