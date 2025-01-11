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

namespace watermossmc\network\raklib\server\ipc;

use watermossmc\network\raklib\protocol\EncapsulatedPacket;
use watermossmc\network\raklib\protocol\PacketReliability;
use watermossmc\network\raklib\server\ipc\UserToRakLibThreadMessageProtocol as ITCProtocol;
use watermossmc\network\raklib\server\ServerInterface;
use watermossmc\utils\Binary;

use function chr;
use function strlen;

class UserToRakLibThreadMessageSender implements ServerInterface
{
	public function __construct(
		private InterThreadChannelWriter $channel
	) {
	}

	public function sendEncapsulated(int $sessionId, EncapsulatedPacket $packet, bool $immediate = false) : void
	{
		$flags =
			($immediate ? ITCProtocol::ENCAPSULATED_FLAG_IMMEDIATE : 0) |
			($packet->identifierACK !== null ? ITCProtocol::ENCAPSULATED_FLAG_NEED_ACK : 0);

		$buffer = chr(ITCProtocol::PACKET_ENCAPSULATED) .
			Binary::writeInt($sessionId) .
			chr($flags) .
			chr($packet->reliability) .
			($packet->identifierACK !== null ? Binary::writeInt($packet->identifierACK) : "") .
			(PacketReliability::isSequencedOrOrdered($packet->reliability) ? chr($packet->orderChannel) : "") .
			$packet->buffer;
		$this->channel->write($buffer);
	}

	public function sendRaw(string $address, int $port, string $payload) : void
	{
		$buffer = chr(ITCProtocol::PACKET_RAW) . chr(strlen($address)) . $address . Binary::writeShort($port) . $payload;
		$this->channel->write($buffer);
	}

	public function closeSession(int $sessionId) : void
	{
		$buffer = chr(ITCProtocol::PACKET_CLOSE_SESSION) . Binary::writeInt($sessionId);
		$this->channel->write($buffer);
	}

	public function setName(string $name) : void
	{
		$this->channel->write(chr(ITCProtocol::PACKET_SET_NAME) . $name);
	}

	public function setPortCheck(bool $value) : void
	{
		$this->channel->write(chr($value ? ITCProtocol::PACKET_ENABLE_PORT_CHECK : ITCProtocol::PACKET_DISABLE_PORT_CHECK));
	}

	public function setPacketsPerTickLimit(int $limit) : void
	{
		$this->channel->write(chr(ITCProtocol::PACKET_SET_PACKETS_PER_TICK_LIMIT) . Binary::writeLong($limit));
	}

	public function blockAddress(string $address, int $timeout) : void
	{
		$buffer = chr(ITCProtocol::PACKET_BLOCK_ADDRESS) . chr(strlen($address)) . $address . Binary::writeInt($timeout);
		$this->channel->write($buffer);
	}

	public function unblockAddress(string $address) : void
	{
		$buffer = chr(ITCProtocol::PACKET_UNBLOCK_ADDRESS) . chr(strlen($address)) . $address;
		$this->channel->write($buffer);
	}

	public function addRawPacketFilter(string $regex) : void
	{
		$this->channel->write(chr(ITCProtocol::PACKET_RAW_FILTER) . $regex);
	}
}
