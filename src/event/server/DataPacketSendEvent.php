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
use watermossmc\network\mcpe\protocol\ClientboundPacket;
use watermossmc\utils\Utils;

/**
 * Called when packets are sent to network sessions.
 */
class DataPacketSendEvent extends ServerEvent implements Cancellable
{
	use CancellableTrait;

	/**
	 * @param NetworkSession[]    $targets
	 * @param ClientboundPacket[] $packets
	 */
	public function __construct(
		private array $targets,
		private array $packets
	) {
	}

	/**
	 * @return NetworkSession[]
	 */
	public function getTargets() : array
	{
		return $this->targets;
	}

	/**
	 * @return ClientboundPacket[]
	 */
	public function getPackets() : array
	{
		return $this->packets;
	}

	/**
	 * @param ClientboundPacket[] $packets
	 */
	public function setPackets(array $packets) : void
	{
		Utils::validateArrayValueType($packets, function (ClientboundPacket $_) : void {});
		$this->packets = $packets;
	}
}
