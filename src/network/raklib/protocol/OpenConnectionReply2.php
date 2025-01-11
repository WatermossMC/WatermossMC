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

use watermossmc\network\raklib\utils\InternetAddress;

class OpenConnectionReply2 extends OfflineMessage
{
	public static $ID = MessageIdentifiers::ID_OPEN_CONNECTION_REPLY_2;

	public int $serverID;
	public InternetAddress $clientAddress;
	public int $mtuSize;
	public bool $serverSecurity = false;

	public static function create(int $serverId, InternetAddress $clientAddress, int $mtuSize, bool $serverSecurity) : self
	{
		$result = new self();
		$result->serverID = $serverId;
		$result->clientAddress = $clientAddress;
		$result->mtuSize = $mtuSize;
		$result->serverSecurity = $serverSecurity;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$this->writeMagic($out);
		$out->putLong($this->serverID);
		$out->putAddress($this->clientAddress);
		$out->putShort($this->mtuSize);
		$out->putByte($this->serverSecurity ? 1 : 0);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->readMagic($in);
		$this->serverID = $in->getLong();
		$this->clientAddress = $in->getAddress();
		$this->mtuSize = $in->getShort();
		$this->serverSecurity = $in->getByte() !== 0;
	}
}
