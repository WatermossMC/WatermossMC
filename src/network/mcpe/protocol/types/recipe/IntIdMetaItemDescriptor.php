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

final class IntIdMetaItemDescriptor implements ItemDescriptor
{
	use GetTypeIdFromConstTrait;

	public const ID = ItemDescriptorType::INT_ID_META;

	public function __construct(
		private int $id,
		private int $meta
	) {
		if ($id === 0 && $meta !== 0) {
			throw new \InvalidArgumentException("Meta cannot be non-zero for air");
		}
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getMeta() : int
	{
		return $this->meta;
	}

	public static function read(PacketSerializer $in) : self
	{
		$id = $in->getSignedLShort();
		if ($id !== 0) {
			$meta = $in->getSignedLShort();
		} else {
			$meta = 0;
		}

		return new self($id, $meta);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putLShort($this->id);
		if ($this->id !== 0) {
			$out->putLShort($this->meta);
		}
	}
}
