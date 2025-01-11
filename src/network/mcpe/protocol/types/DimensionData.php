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

final class DimensionData
{
	public function __construct(
		private int $maxHeight,
		private int $minHeight,
		private int $generator
	) {
	}

	public function getMaxHeight() : int
	{
		return $this->maxHeight;
	}

	public function getMinHeight() : int
	{
		return $this->minHeight;
	}

	public function getGenerator() : int
	{
		return $this->generator;
	}

	public static function read(PacketSerializer $in) : self
	{
		$maxHeight = $in->getVarInt();
		$minHeight = $in->getVarInt();
		$generator = $in->getVarInt();

		return new self($maxHeight, $minHeight, $generator);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putVarInt($this->maxHeight);
		$out->putVarInt($this->minHeight);
		$out->putVarInt($this->generator);
	}
}
