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
