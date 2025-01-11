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

namespace watermossmc\network\mcpe\protocol\types\inventory;

use function base64_encode;

final class ItemStack implements \JsonSerializable
{
	/**
	 * @param string $rawExtraData Serialized ItemStackExtraData (use ItemStackExtraData->write())
	 * @see ItemStackExtraData::write()
	 */
	public function __construct(
		private int $id,
		private int $meta,
		private int $count,
		private int $blockRuntimeId,
		private string $rawExtraData,
	) {
	}

	public static function null() : self
	{
		return new self(0, 0, 0, 0, "");
	}

	public function isNull() : bool
	{
		return $this->id === 0;
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getMeta() : int
	{
		return $this->meta;
	}

	public function getCount() : int
	{
		return $this->count;
	}

	public function getBlockRuntimeId() : int
	{
		return $this->blockRuntimeId;
	}

	/**
	 * Decode this into ItemStackExtraData using ItemStackExtraData::read() (or ItemStackExtraDataShield::read() if this
	 * data is for a shield item)
	 * This isn't automatically decoded because it's usually not needed and is sometimes expensive to decode.
	 * @see ItemStackExtraData::read()
	 * @see ItemStackExtraDataShield::read()
	 */
	public function getRawExtraData() : string
	{
		return $this->rawExtraData;
	}

	public function equals(ItemStack $itemStack) : bool
	{
		return $this->equalsWithoutCount($itemStack) && $this->count === $itemStack->count;
	}

	public function equalsWithoutCount(ItemStack $itemStack) : bool
	{
		return
			$this->id === $itemStack->id &&
			$this->meta === $itemStack->meta &&
			$this->blockRuntimeId === $itemStack->blockRuntimeId &&
			$this->rawExtraData === $itemStack->rawExtraData;
	}

	/** @return mixed[] */
	public function jsonSerialize() : array
	{
		return [
			"id" => $this->id,
			"meta" => $this->meta,
			"count" => $this->count,
			"blockRuntimeId" => $this->blockRuntimeId,
			"rawExtraData" => base64_encode($this->rawExtraData),
		];
	}
}
