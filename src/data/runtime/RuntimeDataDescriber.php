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

namespace watermossmc\data\runtime;

use watermossmc\block\utils\BrewingStandSlot;
use watermossmc\block\utils\WallConnectionType;
use watermossmc\math\Facing;

/**
 * Interface implemented by {@link RuntimeDataReader}, {@link RuntimeDataWriter} and {@link RuntimeDataSizeCalculator}.
 * Used to describe the structure of runtime data to an implementation.
 *
 * This interface should be considered **sealed**.
 * You may use it as a type for parameters and return values, but it should not be implemented outside of this package.
 * New methods may be added without warning.
 */
interface RuntimeDataDescriber extends RuntimeEnumDescriber
{
	public function int(int $bits, int &$value) : void;

	/**
	 * @deprecated Use {@link RuntimeDataDescriber::boundedIntAuto()} instead.
	 */
	public function boundedInt(int $bits, int $min, int $max, int &$value) : void;

	/**
	 * Same as boundedInt() but automatically calculates the required number of bits from the range.
	 * The range bounds must be constant.
	 */
	public function boundedIntAuto(int $min, int $max, int &$value) : void;

	public function bool(bool &$value) : void;

	public function horizontalFacing(int &$facing) : void;

	/**
	 * @param int[] $faces
	 */
	public function facingFlags(array &$faces) : void;

	/**
	 * @param int[] $faces
	 */
	public function horizontalFacingFlags(array &$faces) : void;

	public function facing(int &$facing) : void;

	public function facingExcept(int &$facing, int $except) : void;

	public function axis(int &$axis) : void;

	public function horizontalAxis(int &$axis) : void;

	/**
	 * @param WallConnectionType[] $connections
	 * @phpstan-param array<Facing::NORTH|Facing::EAST|Facing::SOUTH|Facing::WEST, WallConnectionType> $connections
	 */
	public function wallConnections(array &$connections) : void;

	/**
	 * @param BrewingStandSlot[] $slots
	 * @phpstan-param array<int, BrewingStandSlot> $slots
	 *
	 * @deprecated Use {@link enumSet()} instead.
	 */
	public function brewingStandSlots(array &$slots) : void;

	public function railShape(int &$railShape) : void;

	public function straightOnlyRailShape(int &$railShape) : void;

	/**
	 * @phpstan-template T of \UnitEnum
	 * @phpstan-param T &$case
	 * @phpstan-param-out T $case
	 */
	public function enum(\UnitEnum &$case) : void;

	/**
	 * @param \UnitEnum[] &$set
	 * @param \UnitEnum[] $allCases
	 *
	 * @phpstan-template T of \UnitEnum
	 * @phpstan-param array<int, T> &$set
	 * @phpstan-param array<int, T> $allCases
	 */
	public function enumSet(array &$set, array $allCases) : void;
}
