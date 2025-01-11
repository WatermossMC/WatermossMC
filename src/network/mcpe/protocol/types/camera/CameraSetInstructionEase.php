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

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class CameraSetInstructionEase
{
	/**
	 * @see CameraSetInstructionEaseType
	 */
	public function __construct(
		private int $type,
		private float $duration
	) {
	}

	/**
	 * @see CameraSetInstructionEaseType
	 */
	public function getType() : int
	{
		return $this->type;
	}

	public function getDuration() : float
	{
		return $this->duration;
	}

	public static function read(PacketSerializer $in) : self
	{
		$type = $in->getByte();
		$duration = $in->getLFloat();
		return new self($type, $duration);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putByte($this->type);
		$out->putLFloat($this->duration);
	}
}
