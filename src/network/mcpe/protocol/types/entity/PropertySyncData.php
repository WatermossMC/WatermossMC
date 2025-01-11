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

namespace watermossmc\network\mcpe\protocol\types\entity;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

use function count;

final class PropertySyncData
{
	/**
	 * @param int[]   $intProperties
	 * @param float[] $floatProperties
	 * @phpstan-param array<int, int> $intProperties
	 * @phpstan-param array<int, float> $floatProperties
	 */
	public function __construct(
		private array $intProperties,
		private array $floatProperties,
	) {
	}

	/**
	 * @return int[]
	 * @phpstan-return array<int, int>
	 */
	public function getIntProperties() : array
	{
		return $this->intProperties;
	}

	/**
	 * @return float[]
	 * @phpstan-return array<int, float>
	 */
	public function getFloatProperties() : array
	{
		return $this->floatProperties;
	}

	public static function read(PacketSerializer $in) : self
	{
		$intProperties = [];
		$floatProperties = [];

		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$intProperties[$in->getUnsignedVarInt()] = $in->getVarInt();
		}
		for ($i = 0, $count = $in->getUnsignedVarInt(); $i < $count; ++$i) {
			$floatProperties[$in->getUnsignedVarInt()] = $in->getLFloat();
		}

		return new self($intProperties, $floatProperties);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putUnsignedVarInt(count($this->intProperties));
		foreach ($this->intProperties as $key => $value) {
			$out->putUnsignedVarInt($key);
			$out->putVarInt($value);
		}
		$out->putUnsignedVarInt(count($this->floatProperties));
		foreach ($this->floatProperties as $key => $value) {
			$out->putUnsignedVarInt($key);
			$out->putLFloat($value);
		}
	}
}
