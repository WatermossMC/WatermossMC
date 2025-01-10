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

enum PredictedResult : int
{
	use PacketIntEnumTrait;

	case FAILURE = 0;
	case SUCCESS = 1;
}
