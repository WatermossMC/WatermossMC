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

use function array_keys;
use function implode;

/**
 * Simple non-curvable rail.
 */
class StraightOnlyRail extends BaseRail
{
	private int $railShape = BlockLegacyMetadata::RAIL_STRAIGHT_NORTH_SOUTH;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->straightOnlyRailShape($this->railShape);
	}

	protected function setShapeFromConnections(array $connections) : void
	{
		$railShape = self::searchState($connections, RailConnectionInfo::CONNECTIONS);
		if ($railShape === null) {
			throw new \InvalidArgumentException("No rail shape matches these connections");
		}
		$this->railShape = $railShape;
	}

	protected function getCurrentShapeConnections() : array
	{
		return RailConnectionInfo::CONNECTIONS[$this->railShape];
	}

	public function getShape() : int
	{
		return $this->railShape;
	}

	/** @return $this */
	public function setShape(int $shape) : self
	{
		if (!isset(RailConnectionInfo::CONNECTIONS[$shape])) {
			throw new \InvalidArgumentException("Invalid rail shape, must be one of " . implode(", ", array_keys(RailConnectionInfo::CONNECTIONS)));
		}
		$this->railShape = $shape;
		return $this;

	}
}
