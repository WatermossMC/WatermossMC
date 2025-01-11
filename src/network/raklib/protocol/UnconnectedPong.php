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

class UnconnectedPong extends OfflineMessage
{
	public static $ID = MessageIdentifiers::ID_UNCONNECTED_PONG;

	public int $sendPingTime;
	public int $serverId;
	public string $serverName;

	public static function create(int $sendPingTime, int $serverId, string $serverName) : self
	{
		$result = new self();
		$result->sendPingTime = $sendPingTime;
		$result->serverId = $serverId;
		$result->serverName = $serverName;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLong($this->sendPingTime);
		$out->putLong($this->serverId);
		$this->writeMagic($out);
		$out->putString($this->serverName);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->sendPingTime = $in->getLong();
		$this->serverId = $in->getLong();
		$this->readMagic($in);
		$this->serverName = $in->getString();
	}
}
