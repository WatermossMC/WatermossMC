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

final class CameraFadeInstructionColor
{
	public function __construct(
		private float $red,
		private float $green,
		private float $blue,
	) {
	}

	public function getRed() : float
	{
		return $this->red;
	}

	public function getGreen() : float
	{
		return $this->green;
	}

	public function getBlue() : float
	{
		return $this->blue;
	}

	public static function read(PacketSerializer $in) : self
	{
		$red = $in->getLFloat();
		$green = $in->getLFloat();
		$blue = $in->getLFloat();
		return new self($red, $green, $blue);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLFloat($this->red);
		$out->putLFloat($this->green);
		$out->putLFloat($this->blue);
	}
}
