<?php

/*
 * This file is part of BedrockProtocol.
 * Copyright (C) 2014-2022 PocketMine Team <https://github.com/pmmp/BedrockProtocol>
 *
 * BedrockProtocol is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace watermossmc\network\mcpe\protocol\types\inventory;

use watermossmc\network\mcpe\protocol\types\PacketIntEnumTrait;

enum InventoryLeftTab : int
{
	use PacketIntEnumTrait;

	case NONE = 0;
	case CONSTRUCTION = 1;
	case EQUIPMENT = 2;
	case ITEMS = 3;
	case NATURE = 4;
	case SEARCH = 5;
	case SURVIVAL = 6;
}
