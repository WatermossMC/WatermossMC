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

use const PHP_INT_MAX;
use const PHP_INT_MIN;

final class LongMetadataProperty implements MetadataProperty
{
	use GetTypeIdFromConstTrait;
	use IntegerishMetadataProperty;

	public const ID = EntityMetadataTypes::LONG;

	protected function min() : int
	{
		return PHP_INT_MIN;
	}

	protected function max() : int
	{
		return PHP_INT_MAX;
	}

	public static function read(PacketSerializer $in) : self
	{
		return new self($in->getVarLong());
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putVarLong($this->value);
	}
}
