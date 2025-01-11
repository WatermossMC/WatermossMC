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
use watermossmc\block\utils\DyeColor;

class CakeWithDyedCandle extends CakeWithCandle
{
	use ColoredTrait;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
	{
		$this->color = DyeColor::WHITE;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	public function getCandle() : Candle
	{
		return VanillaBlocks::DYED_CANDLE()->setColor($this->color);
	}
}
