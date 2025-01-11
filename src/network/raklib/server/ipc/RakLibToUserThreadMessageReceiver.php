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

use function inet_ntop;
use function ord;
use function substr;

final class RakLibToUserThreadMessageReceiver
{
	public function __construct(
		private InterThreadChannelReader $channel
	) {
	}

	public function handle(ServerEventListener $listener) : bool
	{
		if (($packet = $this->channel->read()) !== null) {
			$id = ord($packet[0]);
			$offset = 1;
			if ($id === ITCProtocol::PACKET_ENCAPSULATED) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$buffer = substr($packet, $offset);
				$listener->onPacketReceive($sessionId, $buffer);
			} elseif ($id === ITCProtocol::PACKET_RAW) {
				$len = ord($packet[$offset++]);
				$address = substr($packet, $offset, $len);
				$offset += $len;
				$port = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$payload = substr($packet, $offset);
				$listener->onRawPacketReceive($address, $port, $payload);
			} elseif ($id === ITCProtocol::PACKET_REPORT_BANDWIDTH_STATS) {
				$sentBytes = Binary::readLong(substr($packet, $offset, 8));
				$offset += 8;
				$receivedBytes = Binary::readLong(substr($packet, $offset, 8));
				$listener->onBandwidthStatsUpdate($sentBytes, $receivedBytes);
			} elseif ($id === ITCProtocol::PACKET_OPEN_SESSION) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$len = ord($packet[$offset++]);
				$rawAddr = substr($packet, $offset, $len);
				$offset += $len;
				$address = inet_ntop($rawAddr);
				if ($address === false) {
					throw new \RuntimeException("Unexpected invalid IP address in inter-thread message");
				}
				$port = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$clientID = Binary::readLong(substr($packet, $offset, 8));
				$listener->onClientConnect($sessionId, $address, $port, $clientID);
			} elseif ($id === ITCProtocol::PACKET_CLOSE_SESSION) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$reason = ord($packet[$offset]);
				$listener->onClientDisconnect($sessionId, $reason);
			} elseif ($id === ITCProtocol::PACKET_ACK_NOTIFICATION) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$identifierACK = Binary::readInt(substr($packet, $offset, 4));
				$listener->onPacketAck($sessionId, $identifierACK);
			} elseif ($id === ITCProtocol::PACKET_REPORT_PING) {
				$sessionId = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$pingMS = Binary::readInt(substr($packet, $offset, 4));
				$listener->onPingMeasure($sessionId, $pingMS);
			}

			return true;
		}

		return false;
	}
}
