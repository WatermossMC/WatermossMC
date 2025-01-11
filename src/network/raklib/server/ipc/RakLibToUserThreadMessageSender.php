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

use watermossmc\network\raklib\server\ipc\RakLibToUserThreadMessageProtocol as ITCProtocol;
use watermossmc\network\raklib\server\ServerEventListener;
use watermossmc\utils\Binary;

use function chr;
use function inet_pton;
use function strlen;

final class RakLibToUserThreadMessageSender implements ServerEventListener
{
	public function __construct(
		private InterThreadChannelWriter $channel
	) {
	}

	public function onClientConnect(int $sessionId, string $address, int $port, int $clientId) : void
	{
		$rawAddr = inet_pton($address);
		if ($rawAddr === false) {
			throw new \InvalidArgumentException("Invalid IP address");
		}
		$this->channel->write(
			chr(ITCProtocol::PACKET_OPEN_SESSION) .
			Binary::writeInt($sessionId) .
			chr(strlen($rawAddr)) . $rawAddr .
			Binary::writeShort($port) .
			Binary::writeLong($clientId)
		);
	}

	public function onClientDisconnect(int $sessionId, int $reason) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_CLOSE_SESSION) .
			Binary::writeInt($sessionId) .
			chr($reason)
		);
	}

	public function onPacketReceive(int $sessionId, string $packet) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_ENCAPSULATED) .
			Binary::writeInt($sessionId) .
			$packet
		);
	}

	public function onRawPacketReceive(string $address, int $port, string $payload) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_RAW) .
			chr(strlen($address)) . $address .
			Binary::writeShort($port) .
			$payload
		);
	}

	public function onPacketAck(int $sessionId, int $identifierACK) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_ACK_NOTIFICATION) .
			Binary::writeInt($sessionId) .
			Binary::writeInt($identifierACK)
		);
	}

	public function onBandwidthStatsUpdate(int $bytesSentDiff, int $bytesReceivedDiff) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_REPORT_BANDWIDTH_STATS) .
			Binary::writeLong($bytesSentDiff) .
			Binary::writeLong($bytesReceivedDiff)
		);
	}

	public function onPingMeasure(int $sessionId, int $pingMS) : void
	{
		$this->channel->write(
			chr(ITCProtocol::PACKET_REPORT_PING) .
			Binary::writeInt($sessionId) .
			Binary::writeInt($pingMS)
		);
	}
}
