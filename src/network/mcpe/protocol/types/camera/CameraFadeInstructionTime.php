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

final class CameraFadeInstructionTime
{
	public function __construct(
		private float $fadeInTime,
		private float $stayTime,
		private float $fadeOutTime
	) {
	}

	public function getFadeInTime() : float
	{
		return $this->fadeInTime;
	}

	public function getStayTime() : float
	{
		return $this->stayTime;
	}

	public function getFadeOutTime() : float
	{
		return $this->fadeOutTime;
	}

	public static function read(PacketSerializer $in) : self
	{
		$fadeInTime = $in->getLFloat();
		$stayTime = $in->getLFloat();
		$fadeOutTime = $in->getLFloat();
		return new self($fadeInTime, $stayTime, $fadeOutTime);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLFloat($this->fadeInTime);
		$out->putLFloat($this->stayTime);
		$out->putLFloat($this->fadeOutTime);
	}
}
