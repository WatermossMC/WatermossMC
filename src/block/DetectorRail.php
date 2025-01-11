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

use watermossmc\data\runtime\RuntimeDataDescriber;

class DetectorRail extends StraightOnlyRail
{
	protected bool $activated = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		parent::describeBlockOnlyState($w);
		$w->bool($this->activated);
	}

	public function isActivated() : bool
	{
		return $this->activated;
	}

	/** @return $this */
	public function setActivated(bool $activated) : self
	{
		$this->activated = $activated;
		return $this;
	}
	//TODO
}
