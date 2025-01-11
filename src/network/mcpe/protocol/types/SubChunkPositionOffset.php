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

namespace watermossmc\network\mcpe\protocol\types;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\utils\Binary;
use watermossmc\utils\Limits;

final class SubChunkPositionOffset
{
	public function __construct(
		private int $xOffset,
		private int $yOffset,
		private int $zOffset,
	) {
		self::clampOffset($this->xOffset);
		self::clampOffset($this->yOffset);
		self::clampOffset($this->zOffset);
	}

	private static function clampOffset(int $v) : void
	{
		if ($v < Limits::INT8_MIN || $v > Limits::INT8_MAX) {
			throw new \InvalidArgumentException("Offsets must be within the range of a byte (" . Limits::INT8_MIN . " ... " . Limits::INT8_MAX . ")");
		}
	}

	public function getXOffset() : int
	{
		return $this->xOffset;
	}

	public function getYOffset() : int
	{
		return $this->yOffset;
	}

	public function getZOffset() : int
	{
		return $this->zOffset;
	}

	public static function read(PacketSerializer $in) : self
	{
		$xOffset = Binary::signByte($in->getByte());
		$yOffset = Binary::signByte($in->getByte());
		$zOffset = Binary::signByte($in->getByte());

		return new self($xOffset, $yOffset, $zOffset);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putByte($this->xOffset);
		$out->putByte($this->yOffset);
		$out->putByte($this->zOffset);
	}
}
