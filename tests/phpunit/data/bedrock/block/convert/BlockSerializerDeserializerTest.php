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

namespace watermossmc\data\bedrock\block\convert;

use PHPUnit\Framework\TestCase;
use watermossmc\block\BaseBanner;
use watermossmc\block\Bed;
use watermossmc\block\BlockTypeIds;
use watermossmc\block\CaveVines;
use watermossmc\block\Farmland;
use watermossmc\block\MobHead;
use watermossmc\block\RuntimeBlockStateRegistry;
use watermossmc\data\bedrock\block\BlockStateDeserializeException;
use watermossmc\data\bedrock\block\BlockStateSerializeException;

use function print_r;

final class BlockSerializerDeserializerTest extends TestCase
{
	private BlockStateToObjectDeserializer $deserializer;
	private BlockObjectToStateSerializer $serializer;

	public function setUp() : void
	{
		$this->deserializer = new BlockStateToObjectDeserializer();
		$this->serializer = new BlockObjectToStateSerializer();
	}

	public function testAllKnownBlockStatesSerializableAndDeserializable() : void
	{
		foreach (RuntimeBlockStateRegistry::getInstance()->getAllKnownStates() as $block) {
			try {
				$blockStateData = $this->serializer->serializeBlock($block);
			} catch (BlockStateSerializeException $e) {
				self::fail($e->getMessage());
			}
			try {
				$newBlock = $this->deserializer->deserializeBlock($blockStateData);
			} catch (BlockStateDeserializeException $e) {
				self::fail($e->getMessage());
			}

			if ($block->getTypeId() === BlockTypeIds::POTION_CAULDRON) {
				//this pretends to be a water cauldron in the blockstate, and stores its actual data in the blockentity
				continue;
			}

			//The following are workarounds for differences in blockstate representation in Bedrock vs PM
			//In some cases, some properties are not stored in the blockstate (but rather in the block entity NBT), but
			//they do form part of the internal blockstate hash in PM. In other cases, PM allows representing states
			//that don't exist in Bedrock, such as the cave vines head without berries, which is a state that visually
			//exists in Bedrock, but doesn't have its own ID.
			//This leads to inconsistencies when serializing and deserializing blockstates which we need to correct for.
			if (
				($block instanceof BaseBanner && $newBlock instanceof BaseBanner) ||
				($block instanceof Bed && $newBlock instanceof Bed)
			) {
				$newBlock->setColor($block->getColor());
			} elseif ($block instanceof MobHead && $newBlock instanceof MobHead) {
				$newBlock->setMobHeadType($block->getMobHeadType());
			} elseif ($block instanceof CaveVines && $newBlock instanceof CaveVines && !$block->hasBerries()) {
				$newBlock->setHead($block->isHead());
			} elseif ($block instanceof Farmland && $newBlock instanceof Farmland) {
				$block->setWaterPositionIndex($newBlock->getWaterPositionIndex());
			}

			self::assertSame($block->getStateId(), $newBlock->getStateId(), "Mismatch of blockstate for " . $block->getName() . ", " . print_r($block, true) . " vs " . print_r($newBlock, true));
		}
	}
}
