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

use watermossmc\entity\Squid;
use watermossmc\network\mcpe\protocol\ActorEventPacket;
use watermossmc\network\mcpe\protocol\types\ActorEvent;

final class SquidInkCloudAnimation implements Animation
{
	public function __construct(private Squid $squid)
	{
	}

	public function encode() : array
	{
		return [
			ActorEventPacket::create($this->squid->getId(), ActorEvent::SQUID_INK_CLOUD, 0)
		];
	}
}
