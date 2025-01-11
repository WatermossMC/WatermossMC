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

use watermossmc\block\utils\FacesOppositePlacingPlayerTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;
use watermossmc\math\AxisAlignedBB;
use watermossmc\math\Facing;

class EndPortalFrame extends Opaque
{
	use FacesOppositePlacingPlayerTrait;

	protected bool $eye = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->horizontalFacing($this->facing);
		$w->bool($this->eye);
	}

	public function hasEye() : bool
	{
		return $this->eye;
	}

	/** @return $this */
	public function setEye(bool $eye) : self
	{
		$this->eye = $eye;
		return $this;
	}

	public function getLightLevel() : int
	{
		return 1;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array
	{
		return [AxisAlignedBB::one()->trim(Facing::UP, 3 / 16)];
	}
}
