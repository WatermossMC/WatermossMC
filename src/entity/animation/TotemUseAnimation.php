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

use watermossmc\entity\Human;
use watermossmc\network\mcpe\protocol\ActorEventPacket;
use watermossmc\network\mcpe\protocol\types\ActorEvent;

final class TotemUseAnimation implements Animation
{
	//TODO: check if this can be expanded to more than just humans
	public function __construct(private Human $human)
	{
	}

	public function encode() : array
	{
		return [
			ActorEventPacket::create($this->human->getId(), ActorEvent::CONSUME_TOTEM, 0)
		];
	}
}
