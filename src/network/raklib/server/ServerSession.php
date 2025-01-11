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

namespace watermossmc\network\raklib\server;

use watermossmc\network\raklib\generic\Session;
use watermossmc\network\raklib\protocol\ConnectionRequest;
use watermossmc\network\raklib\protocol\ConnectionRequestAccepted;
use watermossmc\network\raklib\protocol\MessageIdentifiers;
use watermossmc\network\raklib\protocol\NewIncomingConnection;
use watermossmc\network\raklib\protocol\Packet;
use watermossmc\network\raklib\protocol\PacketReliability;
use watermossmc\network\raklib\protocol\PacketSerializer;
use watermossmc\network\raklib\utils\InternetAddress;

use function ord;

class ServerSession extends Session
{
	public const DEFAULT_MAX_SPLIT_PART_COUNT = 128;
	public const DEFAULT_MAX_CONCURRENT_SPLIT_COUNT = 4;

	private Server $server;
	private int $internalId;

	public function __construct(
		Server $server,
		\Logger $logger,
		InternetAddress $address,
		int $clientId,
		int $mtuSize,
		int $internalId,
		int $recvMaxSplitParts = self::DEFAULT_MAX_SPLIT_PART_COUNT,
		int $recvMaxConcurrentSplits = self::DEFAULT_MAX_CONCURRENT_SPLIT_COUNT
	) {
		$this->server = $server;
		$this->internalId = $internalId;
		parent::__construct($logger, $address, $clientId, $mtuSize, $recvMaxSplitParts, $recvMaxConcurrentSplits);
	}

	/**
	 * Returns an ID used to identify this session across threads.
	 */
	public function getInternalId() : int
	{
		return $this->internalId;
	}

	final protected function sendPacket(Packet $packet) : void
	{
		$this->server->sendPacket($packet, $this->address);
	}

	protected function onPacketAck(int $identifierACK) : void
	{
		$this->server->getEventListener()->onPacketAck($this->internalId, $identifierACK);
	}

	protected function onDisconnect(int $reason) : void
	{
		$this->server->getEventListener()->onClientDisconnect($this->internalId, $reason);
	}

	final protected function handleRakNetConnectionPacket(string $packet) : void
	{
		$id = ord($packet[0]);
		if ($id === MessageIdentifiers::ID_CONNECTION_REQUEST) {
			$dataPacket = new ConnectionRequest();
			$dataPacket->decode(new PacketSerializer($packet));
			$this->queueConnectedPacket(ConnectionRequestAccepted::create(
				$this->address,
				[],
				$dataPacket->sendPingTime,
				$this->getRakNetTimeMS()
			), PacketReliability::UNRELIABLE, 0, true);
		} elseif ($id === MessageIdentifiers::ID_NEW_INCOMING_CONNECTION) {
			$dataPacket = new NewIncomingConnection();
			$dataPacket->decode(new PacketSerializer($packet));

			if ($dataPacket->address->getPort() === $this->server->getPort() || !$this->server->portChecking) {
				$this->state = self::STATE_CONNECTED; //FINALLY!
				$this->server->openSession($this);

				//$this->handlePong($dataPacket->sendPingTime, $dataPacket->sendPongTime); //can't use this due to system-address count issues in MCPE >.<
				$this->sendPing();
			}
		}
	}

	protected function onPacketReceive(string $packet) : void
	{
		$this->server->getEventListener()->onPacketReceive($this->internalId, $packet);
	}

	protected function onPingMeasure(int $pingMS) : void
	{
		$this->server->getEventListener()->onPingMeasure($this->internalId, $pingMS);
	}
}
