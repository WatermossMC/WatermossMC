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

use watermossmc\block\utils\FroglightType;
use watermossmc\data\runtime\RuntimeDataDescriber;

final class Froglight extends SimplePillar
{
	private FroglightType $froglightType = FroglightType::OCHRE;

	public function describeBlockItemState(RuntimeDataDescriber $w) : void
	{
		$w->enum($this->froglightType);
	}

	public function getFroglightType() : FroglightType
	{
		return $this->froglightType;
	}

	/** @return $this */
	public function setFroglightType(FroglightType $froglightType) : self
	{
		$this->froglightType = $froglightType;
		return $this;
	}

	public function getLightLevel() : int
	{
		return 15;
	}
}
