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
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class FloatMetadataProperty implements MetadataProperty
{
	use GetTypeIdFromConstTrait;

	public const ID = EntityMetadataTypes::FLOAT;

	public function __construct(
		private float $value
	) {
	}

	public function getValue() : float
	{
		return $this->value;
	}

	public function equals(MetadataProperty $other) : bool
	{
		return $other instanceof self && $other->value === $this->value;
	}

	public static function read(PacketSerializer $in) : self
	{
		return new self($in->getLFloat());
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLFloat($this->value);
	}
}
