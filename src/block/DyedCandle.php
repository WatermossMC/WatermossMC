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

use watermossmc\block\utils\ColoredTrait;

class DyedCandle extends Candle
{
	use ColoredTrait;

	protected function getCandleIfCompatibleType(Block $block) : ?Candle
	{
		$result = parent::getCandleIfCompatibleType($block);
		//different coloured candles can't be combined in the same block
		return $result instanceof DyedCandle && $result->color === $this->color ? $result : null;
	}
}
