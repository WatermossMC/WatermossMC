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

class NetworkStackLatencyPacket extends DataPacket implements ClientboundPacket, ServerboundPacket
{
	public const NETWORK_ID = ProtocolInfo::NETWORK_STACK_LATENCY_PACKET;

	public int $timestamp;
	public bool $needResponse;

	/**
	 * @generate-create-func
	 */
	public static function create(int $timestamp, bool $needResponse) : self
	{
		$result = new self();
		$result->timestamp = $timestamp;
		$result->needResponse = $needResponse;
		return $result;
	}

	public static function request(int $timestamp) : self
	{
		return self::create($timestamp, true);
	}

	public static function response(int $timestamp) : self
	{
		return self::create($timestamp, false);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->timestamp = $in->getLLong();
		$this->needResponse = $in->getBool();
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLLong($this->timestamp);
		$out->putBool($this->needResponse);
	}

	public function handle(PacketHandlerInterface $handler) : bool
	{
		return $handler->handleNetworkStackLatency($this);
	}
}
