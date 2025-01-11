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

namespace watermossmc\block\utils;

use watermossmc\data\runtime\RuntimeDataDescriber;

trait AnalogRedstoneSignalEmitterTrait
{
	protected int $signalStrength = 0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void
	{
		$w->boundedIntAuto(0, 15, $this->signalStrength);
	}

	public function getOutputSignalStrength() : int
	{
		return $this->signalStrength;
	}

	/** @return $this */
	public function setOutputSignalStrength(int $signalStrength) : self
	{
		if ($signalStrength < 0 || $signalStrength > 15) {
			throw new \InvalidArgumentException("Signal strength must be in range 0-15");
		}
		$this->signalStrength = $signalStrength;
		return $this;
	}
}
