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

namespace watermossmc\network\mcpe\protocol\types\inventory\stackresponse;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

final class ItemStackResponseSlotInfo
{
	public function __construct(
		private int $slot,
		private int $hotbarSlot,
		private int $count,
		private int $itemStackId,
		private string $customName,
		private string $filteredCustomName,
		private int $durabilityCorrection
	) {
	}

	public function getSlot() : int
	{
		return $this->slot;
	}

	public function getHotbarSlot() : int
	{
		return $this->hotbarSlot;
	}

	public function getCount() : int
	{
		return $this->count;
	}

	public function getItemStackId() : int
	{
		return $this->itemStackId;
	}

	public function getCustomName() : string
	{
		return $this->customName;
	}

	public function getFilteredCustomName() : string
	{
		return $this->filteredCustomName;
	}

	public function getDurabilityCorrection() : int
	{
		return $this->durabilityCorrection;
	}

	public static function read(PacketSerializer $in) : self
	{
		$slot = $in->getByte();
		$hotbarSlot = $in->getByte();
		$count = $in->getByte();
		$itemStackId = $in->readServerItemStackId();
		$customName = $in->getString();
		$filteredCustomName = $in->getString();
		$durabilityCorrection = $in->getVarInt();
		return new self($slot, $hotbarSlot, $count, $itemStackId, $customName, $filteredCustomName, $durabilityCorrection);
	}

	public function write(PacketSerializer $out) : void
	{
		$out->putByte($this->slot);
		$out->putByte($this->hotbarSlot);
		$out->putByte($this->count);
		$out->writeServerItemStackId($this->itemStackId);
		$out->putString($this->customName);
		$out->putString($this->filteredCustomName);
		$out->putVarInt($this->durabilityCorrection);
	}
}
