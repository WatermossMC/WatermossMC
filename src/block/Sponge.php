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

class Sponge extends Opaque
{
	protected bool $wet = false;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->bool($this->wet);
	}

	public function isWet() : bool
	{
		return $this->wet;
	}

	/** @return $this */
	public function setWet(bool $wet) : self
	{
		$this->wet = $wet;
		return $this;
	}
}
