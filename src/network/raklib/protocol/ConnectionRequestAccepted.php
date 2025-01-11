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

use watermossmc\network\raklib\RakLib;
use watermossmc\network\raklib\utils\InternetAddress;

use function strlen;

class ConnectionRequestAccepted extends ConnectedPacket
{
	public static $ID = MessageIdentifiers::ID_CONNECTION_REQUEST_ACCEPTED;

	public InternetAddress $address;
	/** @var InternetAddress[] */
	public array $systemAddresses = [];
	public int $sendPingTime;
	public int $sendPongTime;

	/**
	 * @param InternetAddress[] $systemAddresses
	 */
	public static function create(InternetAddress $clientAddress, array $systemAddresses, int $sendPingTime, int $sendPongTime) : self
	{
		$result = new self();
		$result->address = $clientAddress;
		$result->systemAddresses = $systemAddresses;
		$result->sendPingTime = $sendPingTime;
		$result->sendPongTime = $sendPongTime;
		return $result;
	}

	public function __construct()
	{
		$this->systemAddresses[] = new InternetAddress("127.0.0.1", 0, 4);
	}

	protected function encodePayload(PacketSerializer $out) : void
	{
		$out->putAddress($this->address);
		$out->putShort(0);

		$dummy = new InternetAddress("0.0.0.0", 0, 4);
		for ($i = 0; $i < RakLib::$SYSTEM_ADDRESS_COUNT; ++$i) {
			$out->putAddress($this->systemAddresses[$i] ?? $dummy);
		}

		$out->putLong($this->sendPingTime);
		$out->putLong($this->sendPongTime);
	}

	protected function decodePayload(PacketSerializer $in) : void
	{
		$this->address = $in->getAddress();
		$in->getShort(); //TODO: check this

		$len = strlen($in->getBuffer());
		$dummy = new InternetAddress("0.0.0.0", 0, 4);

		for ($i = 0; $i < RakLib::$SYSTEM_ADDRESS_COUNT; ++$i) {
			$this->systemAddresses[$i] = $in->getOffset() + 16 < $len ? $in->getAddress() : $dummy; //HACK: avoids trying to read too many addresses on bad data
		}

		$this->sendPingTime = $in->getLong();
		$this->sendPongTime = $in->getLong();
	}
}
