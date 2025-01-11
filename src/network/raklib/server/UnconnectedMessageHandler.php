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

namespace watermossmc\network\raklibserver;

use watermossmc\network\raklib\generic\Session;
use watermossmc\network\raklib\protocol\IncompatibleProtocolVersion;
use watermossmc\network\raklib\protocol\MessageIdentifiers;
use watermossmc\network\raklib\protocol\OfflineMessage;
use watermossmc\network\raklib\protocol\OpenConnectionReply1;
use watermossmc\network\raklib\protocol\OpenConnectionReply2;
use watermossmc\network\raklib\protocol\OpenConnectionRequest1;
use watermossmc\network\raklib\protocol\OpenConnectionRequest2;
use watermossmc\network\raklib\protocol\PacketSerializer;
use watermossmc\network\raklib\protocol\UnconnectedPing;
use watermossmc\network\raklib\protocol\UnconnectedPingOpenConnections;
use watermossmc\network\raklib\protocol\UnconnectedPong;
use watermossmc\network\raklib\utils\InternetAddress;
use watermossmc\utils\BinaryDataException;

use function get_class;
use function min;
use function ord;
use function strlen;
use function substr;

class UnconnectedMessageHandler
{
	/**
	 * @var OfflineMessage[]|\SplFixedArray
	 * @phpstan-var \SplFixedArray<OfflineMessage>
	 */
	private \SplFixedArray $packetPool;

	public function __construct(
		private Server $server,
		private ProtocolAcceptor $protocolAcceptor
	) {
		$this->registerPackets();
	}

	/**
	 * @throws BinaryDataException
	 */
	public function handleRaw(string $payload, InternetAddress $address) : bool
	{
		if ($payload === "") {
			return false;
		}
		$pk = $this->getPacketFromPool($payload);
		if ($pk === null) {
			return false;
		}
		$reader = new PacketSerializer($payload);
		$pk->decode($reader);
		if (!$pk->isValid()) {
			return false;
		}
		if (!$reader->feof()) {
			$remains = substr($reader->getBuffer(), $reader->getOffset());
			$this->server->getLogger()->debug("Still " . strlen($remains) . " bytes unread in " . get_class($pk) . " from $address");
		}
		return $this->handle($pk, $address);
	}

	private function handle(OfflineMessage $packet, InternetAddress $address) : bool
	{
		if ($packet instanceof UnconnectedPing) {
			$this->server->sendPacket(UnconnectedPong::create($packet->sendPingTime, $this->server->getID(), $this->server->getName()), $address);
		} elseif ($packet instanceof OpenConnectionRequest1) {
			if (!$this->protocolAcceptor->accepts($packet->protocol)) {
				$this->server->sendPacket(IncompatibleProtocolVersion::create($this->protocolAcceptor->getPrimaryVersion(), $this->server->getID()), $address);
				$this->server->getLogger()->notice("Refused connection from $address due to incompatible RakNet protocol version (version $packet->protocol)");
			} else {
				//IP header size (20 bytes) + UDP header size (8 bytes)
				$this->server->sendPacket(OpenConnectionReply1::create($this->server->getID(), false, $packet->mtuSize + 28), $address);
			}
		} elseif ($packet instanceof OpenConnectionRequest2) {
			if ($packet->serverAddress->getPort() === $this->server->getPort() || !$this->server->portChecking) {
				if ($packet->mtuSize < Session::MIN_MTU_SIZE) {
					$this->server->getLogger()->debug("Not creating session for $address due to bad MTU size $packet->mtuSize");
					return true;
				}
				$existingSession = $this->server->getSessionByAddress($address);
				if ($existingSession !== null && $existingSession->isConnected()) {
					//for redundancy, in case someone rips up Server - we really don't want connected sessions getting
					//overwritten
					$this->server->getLogger()->debug("Not creating session for $address due to session already opened");
					return true;
				}
				$mtuSize = min($packet->mtuSize, $this->server->getMaxMtuSize()); //Max size, do not allow creating large buffers to fill server memory
				$this->server->sendPacket(OpenConnectionReply2::create($this->server->getID(), $address, $mtuSize, false), $address);
				$this->server->createSession($address, $packet->clientID, $mtuSize);
			} else {
				$this->server->getLogger()->debug("Not creating session for $address due to mismatched port, expected " . $this->server->getPort() . ", got " . $packet->serverAddress->getPort());
			}
		} else {
			return false;
		}

		return true;
	}

	/**
	 * @phpstan-param class-string<OfflineMessage> $class
	 */
	private function registerPacket(int $id, string $class) : void
	{
		$this->packetPool[$id] = new $class();
	}

	public function getPacketFromPool(string $buffer) : ?OfflineMessage
	{
		$pk = $this->packetPool[ord($buffer[0])];
		if ($pk !== null) {
			return clone $pk;
		}

		return null;
	}

	private function registerPackets() : void
	{
		$this->packetPool = new \SplFixedArray(256);

		$this->registerPacket(MessageIdentifiers::ID_UNCONNECTED_PING, UnconnectedPing::class);
		$this->registerPacket(MessageIdentifiers::ID_UNCONNECTED_PING_OPEN_CONNECTIONS, UnconnectedPingOpenConnections::class);
		$this->registerPacket(MessageIdentifiers::ID_OPEN_CONNECTION_REQUEST_1, OpenConnectionRequest1::class);
		$this->registerPacket(MessageIdentifiers::ID_OPEN_CONNECTION_REQUEST_2, OpenConnectionRequest2::class);
	}

}
