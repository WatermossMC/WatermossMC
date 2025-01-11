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

namespace watermossmc\network\raklib\protocol;

class ConnectedPong extends ConnectedPacket
{
	public static $ID = MessageIdentifiers::ID_CONNECTED_PONG;

	public int $sendPingTime;
	public int $sendPongTime;

	public static function create(int $sendPingTime, int $sendPongTime) : self
	{
		$result = new self();
		$result->sendPingTime = $sendPingTime;
		$result->sendPongTime = $sendPongTime;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLong($this->sendPingTime);
		$out->putLong($this->sendPongTime);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->sendPingTime = $in->getLong();
		$this->sendPongTime = $in->getLong();
	}
}
