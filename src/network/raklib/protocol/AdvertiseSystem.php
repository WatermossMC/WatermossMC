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

class AdvertiseSystem extends Packet
{
	public static $ID = MessageIdentifiers::ID_ADVERTISE_SYSTEM;

	public string $serverName;

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putString($this->serverName);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->serverName = $in->getString();
	}
}
