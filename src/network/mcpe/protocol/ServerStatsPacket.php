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

namespace watermossmc\network\mcpe\protocol;

use watermossmc\network\mcpe\protocol\serializer\PacketSerializer;

/**
 * Relays server performance statistics to the client.
 * It's currently unclear what the purpose of this packet is - probably to power some fancy debug screen.
 */
class ServerStatsPacket extends DataPacket implements ClientboundPacket
{
	public const NETWORK_ID = ProtocolInfo::SERVER_STATS_PACKET;

	private float $serverTime;
	private float $networkTime;

	/**
	 * @generate-create-func
	 */
	public static function create(float $serverTime, float $networkTime) : self
	{
		$result = new self();
		$result->serverTime = $serverTime;
		$result->networkTime = $networkTime;
		return $result;
	}

	public function getServerTime() : float
	{
		return $this->serverTime;
	}

	public function getNetworkTime() : float
	{
		return $this->networkTime;
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->serverTime = $in->getLFloat();
		$this->networkTime = $in->getLFloat();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLFloat($this->serverTime);
		$out->putLFloat($this->networkTime);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleServerStats($this);
	}
}
