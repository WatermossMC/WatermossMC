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

namespace watermossmc\block;

use watermossmc\block\utils\CopperMaterial;
use watermossmc\block\utils\CopperOxidation;
use watermossmc\block\utils\CopperTrait;
use watermossmc\block\utils\LightableTrait;
use watermossmc\block\utils\PoweredByRedstoneTrait;
use watermossmc\data\runtime\RuntimeDataDescriber;

class CopperBulb extends Opaque implements CopperMaterial
{
	use CopperTrait;
	use PoweredByRedstoneTrait;
	use LightableTrait{
		describeBlockOnlyState as encodeLitState;
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$this->encodeLitState($w);
		$w->bool($this->powered);
	}

	/** @return $this */
	public function togglePowered(bool $powered) : self
	{
		if ($powered === $this->powered) {
			return $this;
		}
		if ($powered) {
			$this->setLit(!$this->lit);
		}
		$this->setPowered($powered);
		return $this;
	}

	public function getLightLevel() : int
	{
		if ($this->lit) {
			return match($this->oxidation) {
				CopperOxidation::NONE => 15,
				CopperOxidation::EXPOSED => 12,
				CopperOxidation::WEATHERED => 8,
				CopperOxidation::OXIDIZED => 4,
			};
		}

		return 0;
	}
}
