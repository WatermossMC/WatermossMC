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

class OpenConnectionReply1 extends OfflineMessage
{
	public static $ID = MessageIdentifiers::ID_OPEN_CONNECTION_REPLY_1;

	public int $serverID;
	public bool $serverSecurity = false;
	public int $mtuSize;

	public static function create(int $serverId, bool $serverSecurity, int $mtuSize) : self
	{
		$result = new self();
		$result->serverID = $serverId;
		$result->serverSecurity = $serverSecurity;
		$result->mtuSize = $mtuSize;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$this->writeMagic($out);
		$out->putLong($this->serverID);
		$out->putByte($this->serverSecurity ? 1 : 0);
		$out->putShort($this->mtuSize);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->readMagic($in);
		$this->serverID = $in->getLong();
		$this->serverSecurity = $in->getByte() !== 0;
		$this->mtuSize = $in->getShort();
	}
}
