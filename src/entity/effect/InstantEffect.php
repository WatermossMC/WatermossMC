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

namespace watermossmc\entity\effect;

use watermossmc\color\Color;
use watermossmc\lang\Translatable;

abstract class InstantEffect extends Effect
{
	public function __construct(Translatable|string $name, Color $color, bool $bad = false, bool $hasBubbles = true)
	{
		parent::__construct($name, $color, $bad, 1, $hasBubbles);
	}

	public function canTick(EffectInstance $instance) : bool
	{
		return true; //If forced to last longer than 1 tick, these apply every tick.
	}
}
