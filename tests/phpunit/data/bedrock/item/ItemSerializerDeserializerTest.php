<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.watermossmc.net/
 *
 *
 */

declare(strict_types=1);

/*
 * This file is part of the WatermossMC
 * (c) 2025 WatermossMC <gameplaytebakgambard>
 *
 * @License Apache 2.0
 */

namespace watermossmc\data\bedrock\item;

use PHPUnit\Framework\TestCase;
use watermossmc\block\RuntimeBlockStateRegistry;
use watermossmc\item\VanillaItems;
use watermossmc\world\format\io\GlobalBlockStateHandlers;

final class ItemSerializerDeserializerTest extends TestCase
{
	private ItemDeserializer $deserializer;
	private ItemSerializer $serializer;

	public function setUp() : void
	{
		$this->deserializer = new ItemDeserializer(GlobalBlockStateHandlers::getDeserializer());
		$this->serializer = new ItemSerializer(GlobalBlockStateHandlers::getSerializer());
	}

	public function testAllVanillaItemsSerializableAndDeserializable() : void
	{
		foreach (VanillaItems::getAll() as $item) {
			if ($item->isNull()) {
				continue;
			}

			try {
				$itemData = $this->serializer->serializeType($item);
			} catch (ItemTypeSerializeException $e) {
				self::fail($e->getMessage());
			}
			try {
				$newItem = $this->deserializer->deserializeType($itemData);
			} catch (ItemTypeDeserializeException $e) {
				self::fail($e->getMessage());
			}

			self::assertTrue($item->equalsExact($newItem));
		}
	}

	public function testAllVanillaBlocksSerializableAndDeserializable() : void
	{
		foreach (RuntimeBlockStateRegistry::getInstance()->getAllKnownStates() as $block) {
			$item = $block->asItem();
			if ($item->isNull()) {
				continue;
			}

			try {
				$itemData = $this->serializer->serializeType($item);
			} catch (ItemTypeSerializeException $e) {
				self::fail($e->getMessage());
			}
			try {
				$newItem = $this->deserializer->deserializeType($itemData);
			} catch (ItemTypeDeserializeException $e) {
				self::fail($e->getMessage());
			}

			self::assertTrue($item->equalsExact($newItem));
		}
	}
}
