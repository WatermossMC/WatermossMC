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

class ConnectionRequest extends ConnectedPacket
{
	public static $ID = MessageIdentifiers::ID_CONNECTION_REQUEST;

	public int $clientID;
	public int $sendPingTime;
	public bool $useSecurity = false;

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putLong($this->clientID);
		$out->putLong($this->sendPingTime);
		$out->putByte($this->useSecurity ? 1 : 0);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->clientID = $in->getLong();
		$this->sendPingTime = $in->getLong();
		$this->useSecurity = $in->getByte() !== 0;
	}
}
