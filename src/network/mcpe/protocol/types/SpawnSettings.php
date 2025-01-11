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

final class SpawnSettings
{
	public const BIOME_TYPE_DEFAULT = 0;
	public const BIOME_TYPE_USER_DEFINED = 1;

	public function __construct(
		private int $biomeType,
		private string $biomeName,
		private int $dimension
	) {
	}

	public function getBiomeType() : int
	{
		return $this->biomeType;
	}

	public function getBiomeName() : string
	{
		return $this->biomeName;
	}

	/**
	 * @see DimensionIds
	 */
	public function getDimension() : int
	{
		return $this->dimension;
	}

	public static function read(PacketSerializer $in) : self
	{
		$biomeType = $in->getLShort();
		$biomeName = $in->getString();
		$dimension = $in->getVarInt();

		return new self($biomeType, $biomeName, $dimension);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLShort($this->biomeType);
		$out->putString($this->biomeName);
		$out->putVarInt($this->dimension);
	}
}
