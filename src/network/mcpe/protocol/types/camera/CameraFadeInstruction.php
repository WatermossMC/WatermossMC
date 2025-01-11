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
use watermossmc\network\mcpe\protocol\types\camera\CameraFadeInstructionColor as Color;
use watermossmc\network\mcpe\protocol\types\camera\CameraFadeInstructionTime as Time;

final class CameraFadeInstruction
{
	public function __construct(
		private ?Time $time,
		private ?Color $color,
	) {
	}

	public function getTime() : ?Time
	{
		return $this->time;
	}

	public function getColor() : ?Color
	{
		return $this->color;
	}

	public static function read(PacketSerializer $in) : self
	{
		$time = $in->readOptional(fn () => Time::read($in));
		$color = $in->readOptional(fn () => Color::read($in));
		return new self(
			$time,
			$color
		);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->writeOptional($this->time, fn (Time $v) => $v->write($out));
		$out->writeOptional($this->color, fn (Color $v) => $v->write($out));
	}
}
