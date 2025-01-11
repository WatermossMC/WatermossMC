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

final class IntMetadataProperty implements MetadataProperty
{
	use GetTypeIdFromConstTrait;
	use IntegerishMetadataProperty;

	public const ID = EntityMetadataTypes::INT;

	protected function min() : int
	{
		return -0x80000000;
	}

	protected function max() : int
	{
		return 0x7fffffff;
	}

	public static function read(PacketSerializer $in) : self
	{
		return new self($in->getVarInt());
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putVarInt($this->value);
	}
}
