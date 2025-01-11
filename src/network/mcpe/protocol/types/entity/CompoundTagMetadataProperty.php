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

use watermossmc\network\mcpe\protocol\PacketDecodeException;
use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;
use watermossmc\network\mcpe\protocol\types\CacheableNbt;
use watermossmc\network\mcpe\protocol\types\GetTypeIdFromConstTrait;

final class CompoundTagMetadataProperty implements MetadataProperty
{
	use GetTypeIdFromConstTrait;

	public const ID = EntityMetadataTypes::COMPOUND_TAG;

	/** @phpstan-var CacheableNbt<\watermossmc\nbt\tag\CompoundTag> */
	private CacheableNbt $value;

	/**
	 * @phpstan-param CacheableNbt<\watermossmc\nbt\tag\CompoundTag> $value
	 */
	public function __construct(CacheableNbt $value)
	{
		$this->value = clone $value;
	}

	/**
	 * @phpstan-return CacheableNbt<\watermossmc\nbt\tag\CompoundTag>
	 */
	public function getValue() : CacheableNbt
	{
		return clone $this->value;
	}

	public function equals(MetadataProperty $other) : bool
	{
		return $other instanceof self && $other->value->getRoot()->equals($this->value->getRoot());
	}

	/**
	 * @throws PacketDecodeException
	 */
	public static function read(PacketSerializer $in) : self
	{
		return new self(new CacheableNbt($in->getNbtCompoundRoot()));
	}

	public function write(PacketSerializer $out) : void
	{
		$out->put($this->value->getEncodedNbt());
	}
}
