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

namespace watermossmc\entity\animation;

use watermossmc\entity\Living;
use watermossmc\network\mcpe\protocol\AnimatePacket;

final class CriticalHitAnimation implements Animation
{
	public function __construct(private Living $entity)
	{
	}

	public function encode() : array
	{
		return [
			AnimatePacket::create($this->entity->getId(), AnimatePacket::ACTION_CRITICAL_HIT)
		];
	}
}
