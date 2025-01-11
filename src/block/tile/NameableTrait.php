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

namespace watermossmc\block\tile;

use watermossmc\item\Item;
use watermossmc\nbt\tag\CompoundTag;
use watermossmc\nbt\tag\StringTag;

/**
 * This trait implements most methods in the {@link Nameable} interface. It should only be used by Tiles.
 */
trait NameableTrait
{
	/** @var string|null */
	private $customName = null;

	abstract public function getDefaultName() : string;

	public function getName() : string
	{
		return $this->customName ?? $this->getDefaultName();
	}

	public function setName(string $name) : void
	{
		if ($name === "") {
			$this->customName = null;
		} else {
			$this->customName = $name;
		}
	}

	public function hasName() : bool
	{
		return $this->customName !== null;
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void
	{
		if ($this->customName !== null) {
			$nbt->setString(Nameable::TAG_CUSTOM_NAME, $this->customName);
		}
	}

	protected function loadName(CompoundTag $tag) : void
	{
		if (($customNameTag = $tag->getTag(Nameable::TAG_CUSTOM_NAME)) instanceof StringTag) {
			$this->customName = $customNameTag->getValue();
		}
	}

	protected function saveName(CompoundTag $tag) : void
	{
		if ($this->customName !== null) {
			$tag->setString(Nameable::TAG_CUSTOM_NAME, $this->customName);
		}
	}

	/**
	 * @see Tile::copyDataFromItem()
	 */
	public function copyDataFromItem(Item $item) : void
	{
		parent::copyDataFromItem($item);
		if ($item->hasCustomName()) { //this should take precedence over saved NBT
			$this->setName($item->getCustomName());
		}
	}
}
