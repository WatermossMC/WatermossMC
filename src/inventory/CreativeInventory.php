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

namespace watermossmc\inventory;

use watermossmc\crafting\CraftingManagerFromDataHelper;
use watermossmc\crafting\json\ItemStackData;
use watermossmc\data\bedrock\BedrockDataFiles;
use watermossmc\item\Item;
use watermossmc\utils\DestructorCallbackTrait;
use watermossmc\utils\ObjectSet;
use watermossmc\utils\SingletonTrait;
use watermossmc\utils\Utils;

final class CreativeInventory
{
	use SingletonTrait;
	use DestructorCallbackTrait;

	/**
	 * @var Item[]
	 * @phpstan-var array<int, Item>
	 */
	private array $creative = [];

	/** @phpstan-var ObjectSet<\Closure() : void> */
	private ObjectSet $contentChangedCallbacks;

	private function __construct()
	{
		$this->contentChangedCallbacks = new ObjectSet();
		$creativeItems = CraftingManagerFromDataHelper::loadJsonArrayOfObjectsFile(
			BedrockDataFiles::CREATIVEITEMS_JSON,
			ItemStackData::class
		);
		foreach ($creativeItems as $data) {
			$item = CraftingManagerFromDataHelper::deserializeItemStack($data);
			if ($item === null) {
				//unknown item
				continue;
			}
			$this->add($item);
		}
	}

	/**
	 * Removes all previously added items from the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 */
	public function clear() : void
	{
		$this->creative = [];
		$this->onContentChange();
	}

	/**
	 * @return Item[]
	 * @phpstan-return array<int, Item>
	 */
	public function getAll() : array
	{
		return Utils::cloneObjectArray($this->creative);
	}

	public function getItem(int $index) : ?Item
	{
		return isset($this->creative[$index]) ? clone $this->creative[$index] : null;
	}

	public function getItemIndex(Item $item) : int
	{
		foreach ($this->creative as $i => $d) {
			if ($item->equals($d, true, false)) {
				return $i;
			}
		}

		return -1;
	}

	/**
	 * Adds an item to the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 */
	public function add(Item $item) : void
	{
		$this->creative[] = clone $item;
		$this->onContentChange();
	}

	/**
	 * Removes an item from the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 */
	public function remove(Item $item) : void
	{
		$index = $this->getItemIndex($item);
		if ($index !== -1) {
			unset($this->creative[$index]);
			$this->onContentChange();
		}
	}

	public function contains(Item $item) : bool
	{
		return $this->getItemIndex($item) !== -1;
	}

	/** @phpstan-return ObjectSet<\Closure() : void> */
	public function getContentChangedCallbacks() : ObjectSet
	{
		return $this->contentChangedCallbacks;
	}

	private function onContentChange() : void
	{
		foreach ($this->contentChangedCallbacks as $callback) {
			$callback();
		}
	}
}
