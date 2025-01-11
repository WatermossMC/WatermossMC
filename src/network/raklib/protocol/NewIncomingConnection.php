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

use watermossmc\network\naklib\RakLib;
use watermossmc\network\raklib\utils\InternetAddress;

use function strlen;

class NewIncomingConnection extends ConnectedPacket
{
	public static $ID = MessageIdentifiers::ID_NEW_INCOMING_CONNECTION;

	public InternetAddress $address;
	/** @var InternetAddress[] */
	public array $systemAddresses = [];
	public int $sendPingTime;
	public int $sendPongTime;

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putAddress($this->address);
		foreach ($this->systemAddresses as $address) {
			$out->putAddress($address);
		}
		$out->putLong($this->sendPingTime);
		$out->putLong($this->sendPongTime);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->address = $in->getAddress();

		//TODO: HACK!
		$stopOffset = strlen($in->getBuffer()) - 16; //buffer length - sizeof(sendPingTime) - sizeof(sendPongTime)
		$dummy = new InternetAddress("0.0.0.0", 0, 4);
		for ($i = 0; $i < RakLib::$SYSTEM_ADDRESS_COUNT; ++$i) {
			if ($in->getOffset() >= $stopOffset) {
				$this->systemAddresses[$i] = clone $dummy;
			} else {
				$this->systemAddresses[$i] = $in->getAddress();
			}
		}

		$this->sendPingTime = $in->getLong();
		$this->sendPongTime = $in->getLong();
	}
}
