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

namespace watermossmc\network\mcpe\protocol\types\recipe;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class StringIdMetaItemDescriptor implements ItemDescriptor
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemDescriptorType::STRING_ID_META;

	public function __construct(
		private string $id,
		private int $meta
	) {
		if ($meta < 0) {
			throw new \InvalidArgumentException("Meta cannot be negative");
		}
	}

	public function getId() : string
	{
		return $this->id;
	}

	public function getMeta() : int
	{
		return $this->meta;
	}

	public static function read(PacketSerializer $in) : self
	{
		$stringId = $in->getString();
		$meta = $in->getLShort();

		return new self($stringId, $meta);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putString($this->id);
		$out->putLShort($this->meta);
	}
}
