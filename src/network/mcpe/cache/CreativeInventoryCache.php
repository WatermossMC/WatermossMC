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

namespace watermossmc\network\mcpe\cache;

use watermossmc\inventory\CreativeInventory;
use watermossmc\network\mcpe\convert\TypeConverter;
use watermossmc\network\mcpe\protocol\CreativeContentPacket;
use watermossmc\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use watermossmc\utils\SingletonTrait;

use function spl_object_id;

final class CreativeInventoryCache
{
	use SingletonTrait;

	/**
	 * @var CreativeContentPacket[]
	 * @phpstan-var array<int, CreativeContentPacket>
	 */
	private array $caches = [];

	public function getCache(CreativeInventory $inventory) : CreativeContentPacket
	{
		$id = spl_object_id($inventory);
		if (!isset($this->caches[$id])) {
			$inventory->getDestructorCallbacks()->add(function () use ($id) : void {
				unset($this->caches[$id]);
			});
			$inventory->getContentChangedCallbacks()->add(function () use ($id) : void {
				unset($this->caches[$id]);
			});
			$this->caches[$id] = $this->buildCreativeInventoryCache($inventory);
		}
		return $this->caches[$id];
	}

	/**
	 * Rebuild the cache for the given inventory.
	 */
	private function buildCreativeInventoryCache(CreativeInventory $inventory) : CreativeContentPacket
	{
		$entries = [];
		$typeConverter = TypeConverter::getInstance();
		//creative inventory may have holes if items were unregistered - ensure network IDs used are always consistent
		foreach ($inventory->getAll() as $k => $item) {
			$entries[] = new CreativeContentEntry($k, $typeConverter->coreItemStackToNet($item));
		}

		return CreativeContentPacket::create($entries);
	}
}
