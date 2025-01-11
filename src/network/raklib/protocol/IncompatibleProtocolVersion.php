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

class IncompatibleProtocolVersion extends OfflineMessage
{
	public static $ID = MessageIdentifiers::ID_INCOMPATIBLE_PROTOCOL_VERSION;

	public int $protocolVersion;
	public int $serverId;

	public static function create(int $protocolVersion, int $serverId) : self
	{
		$result = new self();
		$result->protocolVersion = $protocolVersion;
		$result->serverId = $serverId;
		return $result;
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putByte($this->protocolVersion);
		$this->writeMagic($out);
		$out->putLong($this->serverId);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->protocolVersion = $in->getByte();
		$this->readMagic($in);
		$this->serverId = $in->getLong();
	}
}
