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

use watermossmc\math\Vector3;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

class Vec3MetadataProperty implements MetadataProperty
{
	use GetTypeIdFromConstTrait;

	public const ID = EntityMetadataTypes::VECTOR3F;

	private Vector3 $value;

	public function __construct(Vector3 $value)
	{
		$this->value = $value->asVector3();
	}

	public function getValue() : Vector3
	{
		return clone $this->value;
	}

	public static function read(PacketSerializer $in) : self
	{
		return new self($in->getVector3());
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putVector3($this->value);
	}

	public function equals(MetadataProperty $other) : bool
	{
		return $other instanceof self && $other->value->equals($this->value);
	}
}
