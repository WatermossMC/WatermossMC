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

final class CameraSetInstructionRotation
{
	public function __construct(
		private float $pitch,
		private float $yaw,
	) {
	}

	public function getPitch() : float
	{
		return $this->pitch;
	}

	public function getYaw() : float
	{
		return $this->yaw;
	}

	public static function read(PacketSerializer $in) : self
	{
		$pitch = $in->getLFloat();
		$yaw = $in->getLFloat();
		return new self($pitch, $yaw);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLFloat($this->pitch);
		$out->putLFloat($this->yaw);
	}
}
