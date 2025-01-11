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

namespace watermossmc\event\server;

use watermossmc\event\Cancellable;
use watermossmc\event\CancellableTrait;
use watermossmc\network\mcpe\NetworkSession;

/**
 * Called before a packet is decoded and handled by the network session.
 * Cancelling this event will drop the packet without decoding it, minimizing wasted CPU time.
 */
class DataPacketDecodeEvent extends ServerEvent implements Cancellable
{
	use CancellableTrait;

	public function __construct(
		private NetworkSession $origin,
		private int $packetId,
		private string $packetBuffer
	) {
	}

	public function getOrigin() : NetworkSession
	{
		return $this->origin;
	}

	public function getPacketId() : int
	{
		return $this->packetId;
	}

	public function getPacketBuffer() : string
	{
		return $this->packetBuffer;
	}
}
