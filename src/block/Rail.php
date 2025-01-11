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

namespace watermossmc\block;

use watermossmc\block\utils\RailConnectionInfo;
use watermossmc\data\bedrock\block\BlockLegacyMetadata;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\math\Facing;

use function array_keys;
use function implode;

class Rail extends BaseRail
{
	private int $railShape = BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->railShape($this->railShape);
	}

	protected function setShapeFromConnections(array $connections) : void
	{
		$railShape = self::searchState($connections, RailConnectionInfo::CONNECTIONS) ?? self::searchState($connections, RailConnectionInfo::CURVE_CONNECTIONS);
		if ($railShape === null) {
			throw new \InvalidArgumentException("No rail shape matches these connections");
		}
		$this->railShape = $railShape;
	}

	protected function getCurrentShapeConnections() : array
	{
		return RailConnectionInfo::CURVE_CONNECTIONS[$this->railShape] ?? RailConnectionInfo::CONNECTIONS[$this->railShape];
	}

	protected function getPossibleConnectionDirectionsOneConstraint(int $constraint) : array
	{
		$possible = parent::getPossibleConnectionDirectionsOneConstraint($constraint);

		if (($constraint & RailConnectionInfo::FLAG_ASCEND) === 0) {
			foreach ([
				Facing::NORTH,
				Facing::SOUTH,
				Facing::WEST,
				Facing::EAST
			] as $d) {
				if ($constraint !== $d) {
					$possible[$d] = true;
				}
			}
		}

		return $possible;
	}

	public function getShape() : int
	{
		return $this->railShape;
	}

	/** @return $this */
	public function setShape(int $shape) : self
	{
		if (!isset(RailConnectionInfo::CONNECTIONS[$shape]) && !isset(RailConnectionInfo::CURVE_CONNECTIONS[$shape])) {
			throw new \InvalidArgumentException("Invalid shape, must be one of " . implode(", ", [...array_keys(RailConnectionInfo::CONNECTIONS), ...array_keys(RailConnectionInfo::CURVE_CONNECTIONS)]));
		}
		$this->railShape = $shape;
		return $this;
	}
}
