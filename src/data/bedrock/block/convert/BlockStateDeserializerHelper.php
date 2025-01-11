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

use watermossmc\block\Block;
use watermossmc\block\Button;
use watermossmc\block\Candle;
use watermossmc\block\Crops;
use watermossmc\block\DaylightSensor;
use watermossmc\block\Door;
use watermossmc\block\DoublePlant;
use watermossmc\block\FenceGate;
use watermossmc\block\FloorCoralFan;
use watermossmc\block\FloorSign;
use watermossmc\block\ItemFrame;
use watermossmc\block\Leaves;
use watermossmc\block\Liquid;
use watermossmc\block\RedMushroomBlock;
use watermossmc\block\RedstoneComparator;
use watermossmc\block\RedstoneRepeater;
use watermossmc\block\Sapling;
use watermossmc\block\SimplePressurePlate;
use watermossmc\block\Slab;
use watermossmc\block\Stair;
use watermossmc\block\Stem;
use watermossmc\block\Trapdoor;
use watermossmc\block\utils\CopperMaterial;
use watermossmc\block\utils\CopperOxidation;
use watermossmc\block\utils\SlabType;
use watermossmc\block\Wall;
use watermossmc\block\WallSign;
use watermossmc\block\WeightedPressurePlate;
use watermossmc\block\Wood;
use watermossmc\data\bedrock\block\BlockLegacyMetadata;
use watermossmc\data\bedrock\block\BlockStateDeserializeException;
use watermossmc\data\bedrock\block\BlockStateNames;
use watermossmc\data\bedrock\block\BlockStateNames as StateNames;
use watermossmc\data\bedrock\MushroomBlockTypeIdMap;
use watermossmc\math\Axis;
use watermossmc\math\Facing;
use watermossmc\utils\AssumptionFailedError;

final class BlockStateDeserializerHelper
{
	/** @throws BlockStateDeserializeException */
	public static function decodeButton(Button $block, BlockStateReader $in) : Button
	{
		return $block
			->setFacing($in->readFacingDirection())
			->setPressed($in->readBool(BlockStateNames::BUTTON_PRESSED_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeCandle(Candle $block, BlockStateReader $in) : Candle
	{
		return $block
			->setCount($in->readBoundedInt(StateNames::CANDLES, 0, 3) + 1)
			->setLit($in->readBool(StateNames::LIT));
	}

	/**
	 * @phpstan-template TCrops of Crops
	 * @phpstan-param TCrops $block
	 * @phpstan-return TCrops
	 *
	 * @throws BlockStateDeserializeException
	 */
	public static function decodeCrops(Crops $block, BlockStateReader $in) : Crops
	{
		return $block->setAge($in->readBoundedInt(BlockStateNames::GROWTH, 0, 7));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeComparator(RedstoneComparator $block, BlockStateReader $in) : RedstoneComparator
	{
		return $block
			->setFacing($in->readCardinalHorizontalFacing())
			->setPowered($in->readBool(BlockStateNames::OUTPUT_LIT_BIT))
			->setSubtractMode($in->readBool(BlockStateNames::OUTPUT_SUBTRACT_BIT));
	}

	/**
	 * @phpstan-template TBlock of CopperMaterial
	 *
	 * @phpstan-param TBlock $block
	 * @phpstan-return TBlock
	 */
	public static function decodeCopper(CopperMaterial $block, CopperOxidation $oxidation) : CopperMaterial
	{
		$block->setOxidation($oxidation);
		$block->setWaxed(false);
		return $block;
	}

	/**
	 * @phpstan-template TBlock of CopperMaterial
	 *
	 * @phpstan-param TBlock $block
	 * @phpstan-return TBlock
	 */
	public static function decodeWaxedCopper(CopperMaterial $block, CopperOxidation $oxidation) : CopperMaterial
	{
		$block->setOxidation($oxidation);
		$block->setWaxed(true);
		return $block;
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDaylightSensor(DaylightSensor $block, BlockStateReader $in) : DaylightSensor
	{
		return $block
			->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDoor(Door $block, BlockStateReader $in) : Door
	{
		//TODO: check if these need any special treatment to get the appropriate data to both halves of the door
		return $block
			->setTop($in->readBool(BlockStateNames::UPPER_BLOCK_BIT))
			->setFacing(Facing::rotateY($in->readLegacyHorizontalFacing(), false))
			->setHingeRight($in->readBool(BlockStateNames::DOOR_HINGE_BIT))
			->setOpen($in->readBool(BlockStateNames::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDoublePlant(DoublePlant $block, BlockStateReader $in) : DoublePlant
	{
		return $block
			->setTop($in->readBool(BlockStateNames::UPPER_BLOCK_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeFenceGate(FenceGate $block, BlockStateReader $in) : FenceGate
	{
		return $block
			->setFacing($in->readLegacyHorizontalFacing())
			->setInWall($in->readBool(BlockStateNames::IN_WALL_BIT))
			->setOpen($in->readBool(BlockStateNames::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeFloorCoralFan(FloorCoralFan $block, BlockStateReader $in) : FloorCoralFan
	{
		return $block
			->setAxis(match($in->readBoundedInt(BlockStateNames::CORAL_FAN_DIRECTION, 0, 1)) {
				0 => Axis::X,
				1 => Axis::Z,
				default => throw new AssumptionFailedError("readBoundedInt() should have prevented this"),
			});
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeFloorSign(FloorSign $block, BlockStateReader $in) : FloorSign
	{
		return $block
			->setRotation($in->readBoundedInt(BlockStateNames::GROUND_SIGN_DIRECTION, 0, 15));
	}

	public static function decodeItemFrame(ItemFrame $block, BlockStateReader $in) : ItemFrame
	{
		$in->todo(StateNames::ITEM_FRAME_PHOTO_BIT); //TODO: not sure what the point of this is
		return $block
			->setFacing($in->readFacingDirection())
			->setHasMap($in->readBool(StateNames::ITEM_FRAME_MAP_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLeaves(Leaves $block, BlockStateReader $in) : Leaves
	{
		return $block
			->setNoDecay($in->readBool(StateNames::PERSISTENT_BIT))
			->setCheckDecay($in->readBool(StateNames::UPDATE_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLiquid(Liquid $block, BlockStateReader $in, bool $still) : Liquid
	{
		$fluidHeightState = $in->readBoundedInt(BlockStateNames::LIQUID_DEPTH, 0, 15);
		return $block
			->setDecay($fluidHeightState & 0x7)
			->setFalling(($fluidHeightState & 0x8) !== 0)
			->setStill($still);
	}

	public static function decodeFlowingLiquid(Liquid $block, BlockStateReader $in) : Liquid
	{
		return self::decodeLiquid($block, $in, false);
	}

	public static function decodeStillLiquid(Liquid $block, BlockStateReader $in) : Liquid
	{
		return self::decodeLiquid($block, $in, true);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeLog(Wood $block, bool $stripped, BlockStateReader $in) : Wood
	{
		return $block
			->setAxis($in->readPillarAxis())
			->setStripped($stripped);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeMushroomBlock(RedMushroomBlock $block, BlockStateReader $in) : Block
	{
		switch ($type = $in->readBoundedInt(BlockStateNames::HUGE_MUSHROOM_BITS, 0, 15)) {
			case BlockLegacyMetadata::MUSHROOM_BLOCK_ALL_STEM:
			case BlockLegacyMetadata::MUSHROOM_BLOCK_STEM: throw new BlockStateDeserializeException("This state does not exist");
			default:
				//invalid types get left as default
				$type = MushroomBlockTypeIdMap::getInstance()->fromId($type);
				return $type !== null ? $block->setMushroomBlockType($type) : $block;
		}
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeRepeater(RedstoneRepeater $block, BlockStateReader $in) : RedstoneRepeater
	{
		return $block
			->setFacing($in->readCardinalHorizontalFacing())
			->setDelay($in->readBoundedInt(BlockStateNames::REPEATER_DELAY, 0, 3) + 1);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeSapling(Sapling $block, BlockStateReader $in) : Sapling
	{
		return $block
			->setReady($in->readBool(BlockStateNames::AGE_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeSimplePressurePlate(SimplePressurePlate $block, BlockStateReader $in) : SimplePressurePlate
	{
		//TODO: not sure what the deal is here ... seems like a mojang bug / artifact of bad implementation?
		//best to keep this separate from weighted plates anyway...
		return $block->setPressed($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15) !== 0);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeSingleSlab(Slab $block, BlockStateReader $in) : Slab
	{
		return $block->setSlabType($in->readSlabPosition());
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeDoubleSlab(Slab $block, BlockStateReader $in) : Slab
	{
		$in->ignored(StateNames::MC_VERTICAL_HALF);
		return $block->setSlabType(SlabType::DOUBLE);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeStairs(Stair $block, BlockStateReader $in) : Stair
	{
		return $block
			->setUpsideDown($in->readBool(BlockStateNames::UPSIDE_DOWN_BIT))
			->setFacing($in->readWeirdoHorizontalFacing());
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeStem(Stem $block, BlockStateReader $in) : Stem
	{
		//In PM, we use Facing::UP to indicate that the stem is not attached to a pumpkin/melon, since this makes the
		//most intuitive sense (the stem is pointing at the sky). However, Bedrock uses the DOWN state for this, which
		//is absurd, and I refuse to make our API similarly absurd.
		$facing = $in->readFacingWithoutUp();
		return self::decodeCrops($block, $in)
			->setFacing($facing === Facing::DOWN ? Facing::UP : $facing);
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeTrapdoor(Trapdoor $block, BlockStateReader $in) : Trapdoor
	{
		return $block
			->setFacing($in->read5MinusHorizontalFacing())
			->setTop($in->readBool(BlockStateNames::UPSIDE_DOWN_BIT))
			->setOpen($in->readBool(BlockStateNames::OPEN_BIT));
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeWall(Wall $block, BlockStateReader $in) : Wall
	{
		$block->setPost($in->readBool(BlockStateNames::WALL_POST_BIT));
		$block->setConnection(Facing::NORTH, $in->readWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_NORTH));
		$block->setConnection(Facing::SOUTH, $in->readWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_SOUTH));
		$block->setConnection(Facing::WEST, $in->readWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_WEST));
		$block->setConnection(Facing::EAST, $in->readWallConnectionType(BlockStateNames::WALL_CONNECTION_TYPE_EAST));

		return $block;
	}

	/** @throws BlockStateDeserializeException */
	public static function decodeWallSign(WallSign $block, BlockStateReader $in) : WallSign
	{
		return $block
			->setFacing($in->readHorizontalFacing());
	}

	public static function decodeWeightedPressurePlate(WeightedPressurePlate $block, BlockStateReader $in) : WeightedPressurePlate
	{
		return $block
			->setOutputSignalStrength($in->readBoundedInt(BlockStateNames::REDSTONE_SIGNAL, 0, 15));
	}
}
